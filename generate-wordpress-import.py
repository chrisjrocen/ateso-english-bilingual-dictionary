#!/usr/bin/env python3
"""
Ateso Dictionary Text to WordPress WXR XML Converter
Parses ateso_dict.txt and generates WordPress import XML file
"""

import re
import html
from datetime import datetime
from xml.sax.saxutils import escape

def parse_entry(entry_text):
    """Parse a single dictionary entry and extract all fields"""
    if not entry_text.strip():
        return None

    # Initialize all fields
    data = {
        'word': '',
        'plural_form': '',
        'part_of_speech': '',
        'gender': 'N/A',
        'dialect_marker': '',
        'primary_definition': '',
        'secondary_definitions': '',
        'cross_references': '',
        'usage_context': '',
        'etymology': '',
        'synonyms': '',
        'examples': []
    }

    # Extract the word (first word before any parenthesis or space)
    word_match = re.match(r'^([^\s(]+)', entry_text)
    if not word_match:
        return None

    data['word'] = word_match.group(1).strip()

    # Extract plural form
    plural_match = re.search(r'\(plural\s+([^)]+)\)', entry_text)
    if plural_match:
        data['plural_form'] = plural_match.group(1).strip()

    # Extract part of speech and gender
    pos_match = re.search(r'\((noun\s+([FMN])|verb|adjective|adverb|preposition|conjunction|interjection|cardinal number)\)', entry_text, re.IGNORECASE)
    if pos_match:
        pos_full = pos_match.group(1).strip()

        # Determine part of speech
        if 'noun' in pos_full.lower():
            data['part_of_speech'] = 'noun'
            # Extract gender
            gender_match = re.search(r'noun\s+([FMN])', pos_full)
            if gender_match:
                gender_letter = gender_match.group(1)
                if gender_letter == 'F':
                    data['gender'] = 'F'
                elif gender_letter == 'M':
                    data['gender'] = 'M'
                else:
                    data['gender'] = 'N/A'
        elif 'verb' in pos_full.lower():
            # Check for verb type
            if 'transitive' in entry_text:
                data['part_of_speech'] = 'verb'
                data['usage_context'] = 'transitive verb'
            elif 'intransitive' in entry_text:
                data['part_of_speech'] = 'verb'
                data['usage_context'] = 'intransitive verb'
            elif 'reflexive' in entry_text:
                data['part_of_speech'] = 'verb'
                data['usage_context'] = 'reflexive verb'
            else:
                data['part_of_speech'] = 'verb'
        elif 'adjective' in pos_full.lower():
            data['part_of_speech'] = 'adjective'
        elif 'adverb' in pos_full.lower():
            data['part_of_speech'] = 'adverb'
        elif 'preposition' in pos_full.lower():
            data['part_of_speech'] = 'preposition'
        elif 'conjunction' in pos_full.lower():
            data['part_of_speech'] = 'conjunction'
        elif 'interjection' in pos_full.lower():
            data['part_of_speech'] = 'interjection'
        elif 'cardinal number' in pos_full.lower():
            data['part_of_speech'] = 'other'

    # Extract dialect marker
    dialect_match = re.search(r'\((Usuk|outside Usuk|Ateso Atororo|[^)]*dialect[^)]*)\)', entry_text, re.IGNORECASE)
    if dialect_match:
        data['dialect_marker'] = dialect_match.group(1).strip()

    # Extract verb stem (for ko-a, ko-o verbs)
    verb_stem_match = re.search(r'(ko-[ao])', entry_text)
    if verb_stem_match:
        data['verb_stem'] = verb_stem_match.group(1)

    # Extract definition (text after all parentheses and before cross-references or examples)
    # Remove the word, plural, POS, gender, dialect from the beginning
    definition_text = entry_text

    # Remove word at beginning
    definition_text = re.sub(r'^[^\s(]+\s*', '', definition_text)

    # Remove plural form
    definition_text = re.sub(r'\(plural[^)]+\)\s*', '', definition_text)

    # Remove POS
    definition_text = re.sub(r'\(noun\s+[FMN]\)\s*', '', definition_text)
    definition_text = re.sub(r'\((transitive|intransitive|reflexive|continuous)?\s*verb\)\s*', '', definition_text)
    definition_text = re.sub(r'\((adjective|adverb|preposition|conjunction|interjection|cardinal number)\)\s*', '', definition_text)

    # Remove dialect marker
    definition_text = re.sub(r'\((Usuk|outside Usuk|Ateso Atororo)\)\s*', '', definition_text)

    # Remove verb stem marker
    definition_text = re.sub(r'ko-[ao]\s*', '', definition_text)

    # Extract cross-references
    cp_match = re.search(r'cp\.\s+([^.]+)', definition_text)
    if cp_match:
        data['cross_references'] = cp_match.group(1).strip()
        # Remove cross-reference from definition
        definition_text = re.sub(r'\s*cp\.\s+[^.]+\.?\s*', '', definition_text)

    # Clean up definition
    definition_text = definition_text.strip()

    # Split definition by semicolons to separate primary and secondary
    definitions = [d.strip() for d in definition_text.split(';') if d.strip()]

    if definitions:
        data['primary_definition'] = definitions[0]
        if len(definitions) > 1:
            data['secondary_definitions'] = '; '.join(definitions[1:])

    return data


def generate_wxr_xml(entries, output_file):
    """Generate WordPress WXR XML file"""

    now = datetime.now()
    pub_date = now.strftime('%a, %d %b %Y %H:%M:%S +0000')

    xml_lines = []

    # XML Header
    xml_lines.append('<?xml version="1.0" encoding="UTF-8" ?>')
    xml_lines.append('<!-- This is a WordPress eXtended RSS (WXR) file -->')
    xml_lines.append('<!-- Generated by Ateso Dictionary Converter -->')
    xml_lines.append('<rss version="2.0"')
    xml_lines.append('\txmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"')
    xml_lines.append('\txmlns:content="http://purl.org/rss/1.0/modules/content/"')
    xml_lines.append('\txmlns:wfw="http://wellformedweb.org/CommentAPI/"')
    xml_lines.append('\txmlns:dc="http://purl.org/dc/elements/1.1/"')
    xml_lines.append('\txmlns:wp="http://wordpress.org/export/1.2/">')
    xml_lines.append('')

    xml_lines.append('<channel>')
    xml_lines.append('\t<title>Ateso-English Dictionary</title>')
    xml_lines.append('\t<link>http://localhost</link>')
    xml_lines.append('\t<description>Bilingual Dictionary</description>')
    xml_lines.append(f'\t<pubDate>{pub_date}</pubDate>')
    xml_lines.append('\t<language>en</language>')
    xml_lines.append('\t<wp:wxr_version>1.2</wp:wxr_version>')
    xml_lines.append('\t<wp:base_site_url>http://localhost</wp:base_site_url>')
    xml_lines.append('\t<wp:base_blog_url>http://localhost</wp:base_blog_url>')
    xml_lines.append('')

    # Add taxonomies
    xml_lines.append('\t<!-- Part of Speech Taxonomy -->')
    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>1</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>part_of_speech</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>noun</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Noun]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>2</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>part_of_speech</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>verb</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Verb]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>3</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>part_of_speech</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>adjective</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Adjective]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>4</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>part_of_speech</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>adverb</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Adverb]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>5</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>part_of_speech</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>other</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Other]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    # Dialect taxonomy
    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>6</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>dialect</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>usuk</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Usuk]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    xml_lines.append('\t<wp:term>')
    xml_lines.append('\t\t<wp:term_id>7</wp:term_id>')
    xml_lines.append('\t\t<wp:term_taxonomy>dialect</wp:term_taxonomy>')
    xml_lines.append('\t\t<wp:term_slug>outside-usuk</wp:term_slug>')
    xml_lines.append('\t\t<wp:term_name><![CDATA[Outside Usuk]]></wp:term_name>')
    xml_lines.append('\t</wp:term>')

    xml_lines.append('')

    # Add entries
    post_id = 1
    for entry in entries:
        if not entry:
            continue

        item_date = now.strftime('%Y-%m-%d %H:%M:%S')

        xml_lines.append('\t<item>')
        xml_lines.append(f'\t\t<title><![CDATA[{entry["word"]}]]></title>')
        xml_lines.append('\t\t<link></link>')
        xml_lines.append(f'\t\t<pubDate>{pub_date}</pubDate>')
        xml_lines.append('\t\t<dc:creator><![CDATA[admin]]></dc:creator>')
        xml_lines.append('\t\t<guid isPermaLink="false"></guid>')
        xml_lines.append('\t\t<description></description>')
        xml_lines.append('\t\t<content:encoded><![CDATA[]]></content:encoded>')
        xml_lines.append('\t\t<excerpt:encoded><![CDATA[]]></excerpt:encoded>')
        xml_lines.append(f'\t\t<wp:post_id>{post_id}</wp:post_id>')
        xml_lines.append(f'\t\t<wp:post_date><![CDATA[{item_date}]]></wp:post_date>')
        xml_lines.append(f'\t\t<wp:post_date_gmt><![CDATA[{item_date}]]></wp:post_date_gmt>')
        xml_lines.append('\t\t<wp:post_modified><![CDATA[0000-00-00 00:00:00]]></wp:post_modified>')
        xml_lines.append('\t\t<wp:post_modified_gmt><![CDATA[0000-00-00 00:00:00]]></wp:post_modified_gmt>')
        xml_lines.append('\t\t<wp:comment_status><![CDATA[closed]]></wp:comment_status>')
        xml_lines.append('\t\t<wp:ping_status><![CDATA[closed]]></wp:ping_status>')
        xml_lines.append(f'\t\t<wp:post_name><![CDATA[{entry["word"].lower()}]]></wp:post_name>')
        xml_lines.append('\t\t<wp:status><![CDATA[publish]]></wp:status>')
        xml_lines.append('\t\t<wp:post_parent>0</wp:post_parent>')
        xml_lines.append('\t\t<wp:menu_order>0</wp:menu_order>')
        xml_lines.append('\t\t<wp:post_type><![CDATA[ateso-words]]></wp:post_type>')
        xml_lines.append('\t\t<wp:post_password><![CDATA[]]></wp:post_password>')
        xml_lines.append('\t\t<wp:is_sticky>0</wp:is_sticky>')

        # Add taxonomies
        if entry['part_of_speech']:
            xml_lines.append('\t\t<category domain="part_of_speech" nicename="' + entry['part_of_speech'] + '"><![CDATA[' + entry['part_of_speech'].title() + ']]></category>')

        if 'Usuk' in entry['dialect_marker']:
            xml_lines.append('\t\t<category domain="dialect" nicename="usuk"><![CDATA[Usuk]]></category>')
        elif 'outside Usuk' in entry['dialect_marker']:
            xml_lines.append('\t\t<category domain="dialect" nicename="outside-usuk"><![CDATA[Outside Usuk]]></category>')

        # Add custom fields (postmeta)
        if entry['plural_form']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[plural_form]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["plural_form"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry.get('verb_stem'):
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[verb_stem]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["verb_stem"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['part_of_speech']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[part_of_speech_select]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["part_of_speech"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['gender']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[gender]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["gender"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['primary_definition']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[primary_definition]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["primary_definition"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['secondary_definitions']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[secondary_definitions]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["secondary_definitions"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['dialect_marker']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[dialect_marker]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["dialect_marker"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['usage_context']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[usage_context]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["usage_context"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        if entry['cross_references']:
            xml_lines.append('\t\t<wp:postmeta>')
            xml_lines.append('\t\t\t<wp:meta_key><![CDATA[cross_references]]></wp:meta_key>')
            xml_lines.append(f'\t\t\t<wp:meta_value><![CDATA[{entry["cross_references"]}]]></wp:meta_value>')
            xml_lines.append('\t\t</wp:postmeta>')

        # Set frequency based on usage indicators (if available)
        xml_lines.append('\t\t<wp:postmeta>')
        xml_lines.append('\t\t\t<wp:meta_key><![CDATA[frequency]]></wp:meta_key>')
        xml_lines.append('\t\t\t<wp:meta_value><![CDATA[common]]></wp:meta_value>')
        xml_lines.append('\t\t</wp:postmeta>')

        xml_lines.append('\t</item>')
        xml_lines.append('')

        post_id += 1

    xml_lines.append('</channel>')
    xml_lines.append('</rss>')

    # Write to file
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(xml_lines))

    print(f"Generated {output_file} with {post_id - 1} entries")


def main():
    """Main function to parse dictionary and generate XML"""

    input_file = 'ateso_dict.txt'
    output_file = 'ateso-dictionary-import.xml'

    print(f"Reading {input_file}...")

    entries = []
    current_entry = ""

    with open(input_file, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()

            # Skip empty lines and section headers
            if not line or re.match(r'^-[A-Z]-\s*$', line):
                if current_entry:
                    parsed = parse_entry(current_entry)
                    if parsed:
                        entries.append(parsed)
                    current_entry = ""
                continue

            # New entry starts if line doesn't start with lowercase or is clearly a new word
            if current_entry and not line[0].islower() and not line[0].isspace():
                # Check if this looks like a continuation or new entry
                # If it starts with a word followed by parentheses, it's likely a new entry
                if re.match(r'^[a-zA-Z-]+(\s+\(|$)', line):
                    parsed = parse_entry(current_entry)
                    if parsed:
                        entries.append(parsed)
                    current_entry = line
                else:
                    current_entry += " " + line
            else:
                if current_entry:
                    current_entry += " " + line
                else:
                    current_entry = line

    # Don't forget the last entry
    if current_entry:
        parsed = parse_entry(current_entry)
        if parsed:
            entries.append(parsed)

    print(f"Parsed {len(entries)} dictionary entries")

    print(f"Generating {output_file}...")
    generate_wxr_xml(entries, output_file)

    print("Done!")
    print(f"\nTo import:")
    print("1. Go to WordPress Admin → Tools → Import")
    print("2. Select 'WordPress' importer")
    print(f"3. Upload {output_file}")
    print("4. Assign posts to a user")
    print("5. Click 'Submit'")


if __name__ == '__main__':
    main()
