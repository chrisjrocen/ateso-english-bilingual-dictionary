/**
 * Ateso Dictionary Frontend
 * Handles search, alphabet browsing, pagination, and result display.
 */
(function () {
	'use strict';

	class DictionaryApp {
		constructor(container) {
			this.container = container;
			this.restUrl = window.atesoDictConfig?.restUrl || container.dataset.restUrl;
			this.dictUrl = window.atesoDictConfig?.dictionaryUrl || '/dictionary/';

			this.searchInput = container.querySelector('.ateso-dict-search-input');
			this.searchSpinner = container.querySelector('.ateso-dict-search-spinner');
			this.resultsContainer = container.querySelector('.ateso-dict-results');
			this.paginationContainer = container.querySelector('.ateso-dict-pagination');
			this.loadingEl = container.querySelector('.ateso-dict-loading');
			this.noResultsEl = container.querySelector('.ateso-dict-no-results');
			this.activeFilterEl = container.querySelector('.ateso-dict-active-filter');
			this.filterTextEl = container.querySelector('.ateso-dict-filter-text');
			this.clearFilterBtn = container.querySelector('.ateso-dict-clear-filter');

			this.currentPage = 1;
			this.perPage = 20;
			this.currentQuery = '';
			this.currentLetter = '';
			this.debounceTimer = null;
			this.isLoading = false;

			this.init();
		}

		init() {
			// Live search with debounce.
			if (this.searchInput) {
				this.searchInput.addEventListener('input', (e) => {
					clearTimeout(this.debounceTimer);
					this.debounceTimer = setTimeout(() => {
						this.currentPage = 1;
						this.currentLetter = '';
						this.currentQuery = e.target.value.trim();
						this.fetchResults();
					}, 300);
				});
			}

			// Alphabet letter clicks.
			this.container.querySelectorAll('.ateso-dict-letter:not(.disabled)').forEach((el) => {
				el.addEventListener('click', (e) => {
					e.preventDefault();
					const letter = el.dataset.letter;
					if (!letter) return;

					this.currentPage = 1;
					this.currentQuery = '';
					this.currentLetter = letter;
					if (this.searchInput) this.searchInput.value = '';
					this.fetchResults();

					// Highlight active letter.
					this.container.querySelectorAll('.ateso-dict-letter').forEach((l) => l.classList.remove('active'));
					el.classList.add('active');
				});
			});

			// Clear filter.
			if (this.clearFilterBtn) {
				this.clearFilterBtn.addEventListener('click', () => {
					this.currentLetter = '';
					this.currentQuery = '';
					this.currentPage = 1;
					if (this.searchInput) this.searchInput.value = '';
					this.container.querySelectorAll('.ateso-dict-letter').forEach((l) => l.classList.remove('active'));
					this.fetchResults();
				});
			}

			// Load initial results (letter A).
			this.currentLetter = 'A';
			const letterA = this.container.querySelector('.ateso-dict-letter[data-letter="A"]');
			if (letterA) letterA.classList.add('active');
			this.fetchResults();
		}

		async fetchResults() {
			if (this.isLoading) return;
			this.isLoading = true;

			this.showLoading(true);
			this.hideNoResults();
			this.updateActiveFilter();

			const params = new URLSearchParams({
				page: this.currentPage,
				per_page: this.perPage,
			});

			if (this.currentQuery) {
				params.set('q', this.currentQuery);
			}
			if (this.currentLetter) {
				params.set('letter', this.currentLetter);
			}

			try {
				const response = await fetch(`${this.restUrl}/search?${params.toString()}`);
				const data = await response.json();

				this.showLoading(false);
				this.isLoading = false;

				if (data.results && data.results.length > 0) {
					this.renderResults(data.results);
					this.renderPagination(data.total, data.pages, data.current_page);
				} else {
					this.resultsContainer.innerHTML = '';
					this.paginationContainer.innerHTML = '';
					this.showNoResults();
				}
			} catch (err) {
				this.showLoading(false);
				this.isLoading = false;
				this.resultsContainer.innerHTML = '<p class="ateso-dict-error">Error loading results. Please try again.</p>';
			}
		}

		renderResults(results) {
			const html = results
				.map((entry) => {
					const homonym = entry.homonym_number ? `<sup>${entry.homonym_number}</sup>` : '';
					const pos = entry.pos ? `<span class="ateso-dict-card-pos">${entry.pos}</span>` : '';
					const gender = entry.gender && entry.gender !== 'N/A' ? `<span class="ateso-dict-card-gender">${entry.gender}</span>` : '';
					const preview = entry.definition_preview
						? `<p class="ateso-dict-card-def">${this.escapeHtml(entry.definition_preview)}</p>`
						: '';

					return `<a href="${this.escapeHtml(entry.url)}" class="ateso-dict-card">
						<div class="ateso-dict-card-header">
							<h3 class="ateso-dict-card-word">${this.escapeHtml(entry.word)}${homonym}</h3>
							<div class="ateso-dict-card-tags">${pos}${gender}</div>
						</div>
						${preview}
						${entry.plural ? `<p class="ateso-dict-card-plural">pl. ${this.escapeHtml(entry.plural)}</p>` : ''}
					</a>`;
				})
				.join('');

			this.resultsContainer.innerHTML = `<div class="ateso-dict-card-grid">${html}</div>`;
		}

		renderPagination(total, pages, current) {
			if (pages <= 1) {
				this.paginationContainer.innerHTML = '';
				return;
			}

			let html = '<nav class="ateso-dict-page-nav" aria-label="Dictionary pagination">';
			html += `<span class="ateso-dict-page-info">${total.toLocaleString()} results</span>`;

			// Previous.
			if (current > 1) {
				html += `<a href="#" class="ateso-dict-page-btn" data-page="${current - 1}">&laquo; Prev</a>`;
			}

			// Page numbers.
			const maxVisible = 5;
			let start = Math.max(1, current - Math.floor(maxVisible / 2));
			let end = Math.min(pages, start + maxVisible - 1);
			if (end - start < maxVisible - 1) {
				start = Math.max(1, end - maxVisible + 1);
			}

			if (start > 1) {
				html += `<a href="#" class="ateso-dict-page-btn" data-page="1">1</a>`;
				if (start > 2) html += '<span class="ateso-dict-page-ellipsis">&hellip;</span>';
			}

			for (let i = start; i <= end; i++) {
				const active = i === current ? ' active' : '';
				html += `<a href="#" class="ateso-dict-page-btn${active}" data-page="${i}">${i}</a>`;
			}

			if (end < pages) {
				if (end < pages - 1) html += '<span class="ateso-dict-page-ellipsis">&hellip;</span>';
				html += `<a href="#" class="ateso-dict-page-btn" data-page="${pages}">${pages}</a>`;
			}

			// Next.
			if (current < pages) {
				html += `<a href="#" class="ateso-dict-page-btn" data-page="${current + 1}">Next &raquo;</a>`;
			}

			html += '</nav>';
			this.paginationContainer.innerHTML = html;

			// Bind pagination clicks.
			this.paginationContainer.querySelectorAll('.ateso-dict-page-btn').forEach((btn) => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					const page = parseInt(btn.dataset.page, 10);
					if (page && page !== this.currentPage) {
						this.currentPage = page;
						this.fetchResults();
						// Scroll to top of results.
						this.container.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				});
			});
		}

		updateActiveFilter() {
			if (!this.activeFilterEl) return;

			if (this.currentLetter) {
				this.filterTextEl.textContent = `Showing words starting with "${this.currentLetter}"`;
				this.activeFilterEl.style.display = '';
			} else if (this.currentQuery) {
				this.filterTextEl.textContent = `Search results for "${this.currentQuery}"`;
				this.activeFilterEl.style.display = '';
			} else {
				this.activeFilterEl.style.display = 'none';
			}
		}

		showLoading(show) {
			if (this.loadingEl) {
				this.loadingEl.style.display = show ? '' : 'none';
			}
			if (this.searchSpinner) {
				this.searchSpinner.style.display = show && this.currentQuery ? '' : 'none';
			}
			if (show) {
				this.resultsContainer.innerHTML = '';
				this.paginationContainer.innerHTML = '';
			}
		}

		showNoResults() {
			if (this.noResultsEl) this.noResultsEl.style.display = '';
		}

		hideNoResults() {
			if (this.noResultsEl) this.noResultsEl.style.display = 'none';
		}

		escapeHtml(str) {
			if (!str) return '';
			const div = document.createElement('div');
			div.textContent = str;
			return div.innerHTML;
		}
	}

	// Initialize on DOM ready.
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.ateso-dictionary-app').forEach(function (el) {
			new DictionaryApp(el);
		});
	});
})();
