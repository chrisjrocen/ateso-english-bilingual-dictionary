#!/usr/bin/env python3
"""
Ateso Dictionary Text-to-JSON Converter
Parses ateso_dict.txt and outputs a validated JSON file for WordPress import.
Handles: homonyms, sub-entries, inline examples, cross-references,
         all POS types, verb stems, dialect markers, usage labels.
"""

import re
import json
import sys
from datetime import datetime, timezone
from collections import Counter


# --- Regex patterns ---

# Section headers like -A-, -B-, etc.
SECTION_HEADER_RE = re.compile(r'^-[A-Z]-\s*$')

# Entry start: word (possibly with trailing digits for homonyms), followed by space
# Handles regular words, hyphenated prefixes (-ce-, -bicil), and words with digits (abeit3)
ENTRY_START_RE = re.compile(r'^-?[a-zA-Z][a-zA-Z\'-]*\d*\s')

# Headword extraction: word + optional homonym number
HEADWORD_RE = re.compile(r'^(-?[a-zA-Z][a-zA-Z\'-]*)(\d+)?\s')

# Plural form: (plural X) or (plural X Y)
PLURAL_RE = re.compile(r'\(plural\s+([^)]+)\)')

# Singular noun: (singular noun F/M)
SINGULAR_NOUN_RE = re.compile(r'\(singular\s+noun\s+([FM]?)\)', re.IGNORECASE)

# Plural noun: (plural noun F/M)
PLURAL_NOUN_RE = re.compile(r'\(plural\s+noun\s+([FM]?)\)', re.IGNORECASE)

# Noun with gender: (noun F), (noun M), (noun neuter), (noun Dimin.), (noun neuter/Dimin.), (noun M/Dimin.)
NOUN_RE = re.compile(
    r'\(noun\s+(F|M|neuter|Dimin\.|neuter/Dimin\.|M/Dimin\.|F/Dimin\.)\)',
    re.IGNORECASE
)

# Collective noun
COLLECTIVE_NOUN_RE = re.compile(r'\(collective\s+noun\s*([FM]?)\)', re.IGNORECASE)

# Verb types
VERB_RE = re.compile(
    r'\((transitive|intransitive|reflexive|causative|continuous|reciprocal)?\s*verb\)',
    re.IGNORECASE
)

# Other POS
OTHER_POS_RE = re.compile(
    r'\((adjective|adverb|preposition|conjunction|interjection|cardinal number|'
    r'interrogation|personal pronoun|demonstrative|indefinite pronoun|'
    r'prefix|suffix|infix|adjectival suffix|suffix, adjective|'
    r'singular imperative|relative pronoun)\)',
    re.IGNORECASE
)

# Dialect markers
DIALECT_RE = re.compile(
    r'\((Usuk|outside Usuk|Ateso Atororo|Serere)\)',
    re.IGNORECASE
)

# Usage labels
USAGE_LABEL_RE = re.compile(
    r'\((jocular|archaic|euphemism|disapprovingly|approvingly|figurative|'
    r'literally|derogatory|biblical|formerly|an insult|Term of abuse)\)',
    re.IGNORECASE
)

# Verb stem patterns: ko-a, ko-o, ki-a, ki-o (appearing after the headword)
VERB_STEM_RE = re.compile(r'\b(ko-[ao]|ki-[ao])\b')

# Cross-reference: cp. word1, word2; or cp. word1. Handle end of string too.
CP_REF_RE = re.compile(r'cp\.\s+([^.;]+?)(?:\.|;|$)')

# Example pattern: ateso_phrase: english_translation
# Examples typically have an Ateso phrase followed by colon and English
# We look for patterns like "word phrase: translation" within definition text
EXAMPLE_RE = re.compile(
    r'([a-zA-Z][a-zA-Z\s\'-]+?)\s*:\s*([^;:.]+(?:\([^)]*\))?[^;:.]*)',
)


def normalize_gender(raw):
    """Normalize gender string to F, M, or N/A."""
    if not raw:
        return None
    raw = raw.strip().upper()
    if raw == 'F':
        return 'F'
    if raw == 'M':
        return 'M'
    if raw in ('NEUTER', 'N/A', 'DIMIN.', 'NEUTER/DIMIN.', 'M/DIMIN.', 'F/DIMIN.'):
        return 'N/A'
    return None


def normalize_pos(raw):
    """Normalize part of speech to a standard category."""
    raw_lower = raw.strip().lower()
    if 'noun' in raw_lower:
        return 'noun'
    # Check adverb BEFORE verb since 'adverb' contains 'verb'
    if 'adverb' in raw_lower:
        return 'adverb'
    if 'verb' in raw_lower:
        return 'verb'
    if 'adjective' in raw_lower or 'adjectival' in raw_lower:
        return 'adjective'
    if 'preposition' in raw_lower:
        return 'preposition'
    if 'conjunction' in raw_lower:
        return 'conjunction'
    if 'interjection' in raw_lower:
        return 'interjection'
    if 'cardinal number' in raw_lower:
        return 'cardinal number'
    if 'pronoun' in raw_lower:
        return 'pronoun'
    if 'prefix' in raw_lower or 'suffix' in raw_lower or 'infix' in raw_lower:
        return 'affix'
    if 'interrogation' in raw_lower:
        return 'interrogation'
    if 'imperative' in raw_lower:
        return 'other'
    return 'other'


def extract_examples_from_text(text, headword):
    """
    Extract inline examples from definition text.
    Examples are typically: ateso_phrase: english_translation
    Returns (cleaned_text, examples_list).
    """
    examples = []

    # Strategy: look for patterns where a known Ateso phrase (often starting with
    # headword or a conjugated form) is followed by a colon and English translation.
    # Common patterns:
    #   headword phrase: english meaning
    #   conjugated_form phrase: translation
    # We need to be careful not to match parts of speech annotations or
    # definition structures that use colons.

    # Split by periods to find potential example sentences
    # An example sentence typically contains a colon separating Ateso from English
    parts = text.split('. ')
    cleaned_parts = []

    for part in parts:
        part = part.strip()
        if not part:
            continue

        # Check if this part looks like an example (contains : with Ateso words on left)
        colon_idx = part.find(':')
        if colon_idx > 0 and colon_idx < len(part) - 1:
            left = part[:colon_idx].strip()
            right = part[colon_idx + 1:].strip()

            # Heuristic: the left side should contain at least 2 words and
            # not be a pure metadata marker. It should look like an Ateso phrase.
            left_words = left.split()

            # Skip if left side is a single known metadata word
            skip_markers = {
                'cp', 'plural', 'noun', 'verb', 'adjective', 'adverb',
                'literally', 'figurative', 'i.e', 'e.g', 'i.e.',
                'Conjugation', 'Imperative', '1st', '2nd', '3rd',
                'a)', 'b)', 'c)', 'd)', 'e)', 'f)',
            }

            is_example = (
                len(left_words) >= 2
                and left_words[0].lower() not in skip_markers
                and not left.startswith('(')
                and not re.match(r'^[a-f]\)', left)
                and not re.match(r'^\d+(st|nd|rd|th)', left)
                # The right side should look like English (contains spaces, common words)
                and len(right.split()) >= 1
            )

            if is_example:
                # Clean up the example
                ateso = left.strip().rstrip(',').strip()
                english = right.strip().rstrip('.').strip()
                if ateso and english:
                    examples.append({
                        'ateso': ateso,
                        'english': english
                    })
                continue

        cleaned_parts.append(part)

    cleaned_text = '. '.join(cleaned_parts)
    if cleaned_text and not cleaned_text.endswith('.'):
        cleaned_text += '.'

    return cleaned_text, examples


def extract_cross_refs(text):
    """Extract cross-references from text. Returns (cleaned_text, refs_list)."""
    refs = []

    def collect_ref(match):
        ref_text = match.group(1).strip()
        # Split by comma or semicolon for multiple refs
        for ref in re.split(r'[,;]', ref_text):
            ref = ref.strip()
            if ref:
                # Clean: remove trailing periods, parentheses content
                ref = re.sub(r'\s*\(.*?\)\s*', '', ref).strip().rstrip('.')
                if ref and len(ref) < 100:  # sanity check
                    refs.append(ref)
        return ''

    cleaned = CP_REF_RE.sub(collect_ref, text)
    return cleaned.strip(), refs


def parse_entry(raw_text, line_number=0):
    """
    Parse a single dictionary entry and extract all structured fields.
    Returns a dict or None if parsing fails.
    """
    text = raw_text.strip()
    if not text:
        return None

    entry = {
        'word': '',
        'homonym_number': None,
        'plural': None,
        'pos': '',
        'pos_detail': None,
        'gender': None,
        'dialect': None,
        'verb_stem': None,
        'usage_labels': [],
        'letter': '',
        'definitions': [],
        'examples': [],
        'sub_entries': [],
        '_line': line_number,
    }

    # --- Extract headword and homonym number ---
    hw_match = HEADWORD_RE.match(text)
    if not hw_match:
        return None

    entry['word'] = hw_match.group(1).strip()
    if hw_match.group(2):
        entry['homonym_number'] = int(hw_match.group(2))

    # Derive letter (first alphabetic character, uppercase)
    first_alpha = ''
    for ch in entry['word']:
        if ch.isalpha():
            first_alpha = ch.upper()
            break
    entry['letter'] = first_alpha if first_alpha else ''

    # Remove headword + homonym from working text
    working = text[hw_match.end():].strip()

    # --- Extract plural form ---
    plural_match = PLURAL_RE.search(working)
    if plural_match:
        entry['plural'] = plural_match.group(1).strip()
        working = working[:plural_match.start()] + working[plural_match.end():]
        working = working.strip()

    # --- Extract part of speech ---
    pos_found = False

    # Check noun patterns (most specific first)
    for pattern, pos_label in [
        (SINGULAR_NOUN_RE, 'singular noun'),
        (PLURAL_NOUN_RE, 'plural noun'),
        (COLLECTIVE_NOUN_RE, 'collective noun'),
        (NOUN_RE, 'noun'),
    ]:
        m = pattern.search(working)
        if m:
            gender_raw = m.group(1) if m.lastindex and m.group(1) else None
            entry['pos'] = 'noun'
            entry['pos_detail'] = pos_label
            entry['gender'] = normalize_gender(gender_raw)
            working = working[:m.start()] + working[m.end():]
            working = working.strip()
            pos_found = True
            break

    if not pos_found:
        # Check verb patterns
        verb_match = VERB_RE.search(working)
        if verb_match:
            verb_type = verb_match.group(1) or ''
            entry['pos'] = 'verb'
            entry['pos_detail'] = (verb_type.lower() + ' verb').strip() if verb_type else 'verb'
            working = working[:verb_match.start()] + working[verb_match.end():]
            working = working.strip()
            pos_found = True

            # Check for a second verb type in the same entry
            verb_match2 = VERB_RE.search(working)
            if verb_match2:
                verb_type2 = verb_match2.group(1) or ''
                if verb_type2:
                    entry['pos_detail'] += ' / ' + verb_type2.lower() + ' verb'
                working = working[:verb_match2.start()] + working[verb_match2.end():]
                working = working.strip()

    if not pos_found:
        # Check other POS
        other_match = OTHER_POS_RE.search(working)
        if other_match:
            raw_pos = other_match.group(1).strip()
            entry['pos'] = normalize_pos(raw_pos)
            entry['pos_detail'] = raw_pos.lower()
            working = working[:other_match.start()] + working[other_match.end():]
            working = working.strip()
            pos_found = True

    # --- Extract dialect ---
    dialect_match = DIALECT_RE.search(working)
    if dialect_match:
        entry['dialect'] = dialect_match.group(1).strip()
        working = working[:dialect_match.start()] + working[dialect_match.end():]
        working = working.strip()

    # --- Extract usage labels ---
    for label_match in USAGE_LABEL_RE.finditer(working):
        entry['usage_labels'].append(label_match.group(1).strip().lower())
    working = USAGE_LABEL_RE.sub('', working).strip()

    # --- Extract verb stem ---
    stem_match = VERB_STEM_RE.search(text[:80])  # Only check near the start
    if stem_match:
        entry['verb_stem'] = stem_match.group(1)
        # Remove verb stem from working text (only first occurrence near start)
        stem_pos = working.find(stem_match.group(1))
        if stem_pos != -1 and stem_pos < 30:
            working = working[:stem_pos] + working[stem_pos + len(stem_match.group(1)):]
            working = working.strip()

    # --- Extract cross-references ---
    working, cp_refs_global = extract_cross_refs(working)

    # --- Extract examples ---
    working, examples = extract_examples_from_text(working, entry['word'])
    entry['examples'] = examples

    # --- Clean up and extract definitions ---
    # Remove leftover empty parentheses and extra whitespace
    working = re.sub(r'\(\s*\)', '', working)
    working = re.sub(r'\s{2,}', ' ', working).strip()
    # Remove leading/trailing punctuation artifacts
    working = working.strip('; .')
    working = working.strip()

    if working:
        # Split by semicolons for multiple definitions
        raw_defs = [d.strip() for d in working.split(';') if d.strip()]

        for i, d in enumerate(raw_defs):
            # Check each definition for its own cp refs
            d_clean, d_refs = extract_cross_refs(d)
            d_clean = d_clean.strip().rstrip('.').strip()

            if not d_clean:
                continue

            def_obj = {
                'text': d_clean,
                'cp_refs': d_refs if d_refs else []
            }
            entry['definitions'].append(def_obj)

        # Attach global cp_refs to the last definition (or first if only one)
        if cp_refs_global and entry['definitions']:
            entry['definitions'][-1]['cp_refs'].extend(cp_refs_global)
        elif cp_refs_global and not entry['definitions']:
            # No definitions but has cp_refs - create a placeholder
            entry['definitions'].append({
                'text': '',
                'cp_refs': cp_refs_global
            })

    # Deduplicate cp_refs
    for d in entry['definitions']:
        d['cp_refs'] = list(dict.fromkeys(d['cp_refs']))

    return entry


def aggregate_entries(filepath):
    """
    Read the dictionary file and aggregate multi-line entries.
    Returns a list of (raw_text, line_number) tuples.
    """
    entries = []
    current_entry = ''
    current_line = 0

    with open(filepath, 'r', encoding='utf-8') as f:
        for line_num, line in enumerate(f, 1):
            stripped = line.strip()

            # Skip blank lines and section headers
            if not stripped or SECTION_HEADER_RE.match(stripped):
                if current_entry:
                    entries.append((current_entry.strip(), current_line))
                    current_entry = ''
                continue

            # Does this line look like a new entry start?
            if ENTRY_START_RE.match(stripped):
                # Save the previous entry if any
                if current_entry:
                    entries.append((current_entry.strip(), current_line))
                current_entry = stripped
                current_line = line_num
            else:
                # Continuation line
                if current_entry:
                    current_entry += ' ' + stripped
                else:
                    # Orphan continuation line — start as new entry
                    current_entry = stripped
                    current_line = line_num

    # Don't forget the last entry
    if current_entry:
        entries.append((current_entry.strip(), current_line))

    return entries


def generate_slug(word, homonym_number):
    """Generate a URL-safe slug from a word and optional homonym number."""
    # Replace non-alphanumeric with hyphens
    slug = re.sub(r'[^a-z0-9]+', '-', word.lower())
    slug = slug.strip('-')
    if homonym_number:
        slug += f'-{homonym_number}'
    return slug


def validate_entry(entry):
    """Validate a parsed entry for completeness. Returns list of issues."""
    issues = []
    if not entry['word']:
        issues.append('Missing word')
    if not entry['letter']:
        issues.append('Missing letter')
    if not entry['pos'] and not entry['definitions']:
        issues.append('No POS and no definitions')
    return issues


def main():
    """Main conversion function."""
    input_file = '../ateso_dict.txt'
    output_file = '../ateso-dictionary-data.json'

    # Allow command-line override
    if len(sys.argv) > 1:
        input_file = sys.argv[1]
    if len(sys.argv) > 2:
        output_file = sys.argv[2]

    print(f'Reading {input_file}...')

    # Step 1: Aggregate multi-line entries
    raw_entries = aggregate_entries(input_file)
    print(f'Aggregated {len(raw_entries)} raw entries')

    # Step 2: Parse each entry
    parsed = []
    failed = []
    for raw_text, line_num in raw_entries:
        entry = parse_entry(raw_text, line_num)
        if entry:
            # Generate slug
            entry['slug'] = generate_slug(entry['word'], entry['homonym_number'])
            # Validate
            issues = validate_entry(entry)
            if issues:
                failed.append({
                    'line': line_num,
                    'text': raw_text[:120],
                    'issues': issues
                })
            parsed.append(entry)
        else:
            failed.append({
                'line': line_num,
                'text': raw_text[:120],
                'issues': ['Failed to parse headword']
            })

    print(f'Successfully parsed {len(parsed)} entries')
    if failed:
        print(f'Entries with issues: {len(failed)}')

    # Step 3: Detect slug collisions and resolve
    slug_counts = Counter(e['slug'] for e in parsed)
    collisions = {s: c for s, c in slug_counts.items() if c > 1}
    if collisions:
        print(f'Slug collisions detected: {len(collisions)}')
        # For collisions without homonym numbers, assign them
        for slug, count in collisions.items():
            matching = [e for e in parsed if e['slug'] == slug]
            # Only auto-fix if none have homonym numbers
            if all(e['homonym_number'] is None for e in matching):
                for i, e in enumerate(matching, 1):
                    e['homonym_number'] = i
                    e['slug'] = generate_slug(e['word'], i)

    # Step 4: Generate statistics
    pos_counts = Counter(e['pos'] for e in parsed)
    letter_counts = Counter(e['letter'] for e in parsed)
    dialect_counts = Counter(e['dialect'] for e in parsed if e['dialect'])
    entries_with_examples = sum(1 for e in parsed if e['examples'])
    entries_with_defs = sum(1 for e in parsed if e['definitions'])
    entries_with_cp = sum(
        1 for e in parsed
        if any(d['cp_refs'] for d in e['definitions'])
    )
    total_definitions = sum(len(e['definitions']) for e in parsed)
    total_examples = sum(len(e['examples']) for e in parsed)

    stats = {
        'total_entries': len(parsed),
        'total_definitions': total_definitions,
        'total_examples': total_examples,
        'entries_with_definitions': entries_with_defs,
        'entries_with_examples': entries_with_examples,
        'entries_with_cross_refs': entries_with_cp,
        'entries_with_issues': len(failed),
        'by_pos': dict(pos_counts.most_common()),
        'by_letter': dict(sorted(letter_counts.items())),
        'by_dialect': dict(dialect_counts.most_common()),
    }

    print('\n--- Statistics ---')
    print(f'Total entries: {stats["total_entries"]}')
    print(f'Total definitions: {stats["total_definitions"]}')
    print(f'Total examples: {stats["total_examples"]}')
    print(f'Entries with definitions: {stats["entries_with_definitions"]}')
    print(f'Entries with examples: {stats["entries_with_examples"]}')
    print(f'Entries with cross-refs: {stats["entries_with_cross_refs"]}')
    print(f'\nBy POS:')
    for pos, count in pos_counts.most_common():
        print(f'  {pos or "(none)"}: {count}')
    print(f'\nBy letter:')
    for letter, count in sorted(letter_counts.items()):
        print(f'  {letter}: {count}')
    print(f'\nBy dialect:')
    for dialect, count in dialect_counts.most_common():
        print(f'  {dialect}: {count}')

    if failed:
        print(f'\n--- Entries with issues (first 20) ---')
        for f_entry in failed[:20]:
            print(f'  Line {f_entry["line"]}: {f_entry["issues"]}')
            print(f'    Text: {f_entry["text"]}')

    # Step 5: Build output
    # Remove internal _line field before output
    for e in parsed:
        e.pop('_line', None)

    output = {
        'metadata': {
            'source': 'ateso_dict.txt',
            'generated_at': datetime.now(timezone.utc).isoformat(),
            'stats': stats,
        },
        'entries': parsed,
    }

    # Step 6: Write JSON
    print(f'\nWriting {output_file}...')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output, f, ensure_ascii=False, indent=2)

    print(f'Done! Generated {output_file}')
    print(f'File contains {len(parsed)} entries')

    return 0 if not failed else 1


if __name__ == '__main__':
    sys.exit(main())
