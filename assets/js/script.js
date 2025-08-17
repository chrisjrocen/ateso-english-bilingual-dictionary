document.addEventListener('DOMContentLoaded', function () {
	const container = document.querySelector('.ateso-words-archive');
	const loading = document.querySelector('.ateso-words-loading');
	const searchInput = document.querySelector('.ateso-words-search');
	const loadMoreBtn = document.querySelector('.ateso-load-more-btn');

	let isLoading = false;
	let offset = 20;
	let allLoaded = false;
	let searchTimeout = null;
	let currentSearch = '';
	let isSearching = false;

	// Check if we have fewer than 20 words initially
	const initialCards = container.querySelectorAll('.ateso-word-card');
	if (initialCards.length < 20 && loadMoreBtn) {
		allLoaded = true;
		loadMoreBtn.style.display = 'none';
	}

	const createCard = (word) => {
		const card = document.createElement('div');
		card.className = 'ateso-word-card';
		card.dataset.title = word.title.toLowerCase();
		card.dataset.meaning = (word.meaning || '').toLowerCase();

		card.innerHTML = `
			<a href="${word.link}">
				<h3>${word.title}</h3>
				<p>${word.meaning || ''}</p>
			</a>
		`;
		return card;
	};

	const showLoadingState = () => {
		if (searchInput) {
			searchInput.classList.add('searching');
		}
		if (loading) {
			loading.style.display = 'block';
			loading.textContent = 'Searching...';
		}
	};

	const hideLoadingState = () => {
		if (searchInput) {
			searchInput.classList.remove('searching');
		}
		if (loading) {
			loading.style.display = 'none';
		}
	};

	const searchWords = async (searchTerm, resetOffset = true) => {
		if (isSearching) return;
		
		isSearching = true;
		showLoadingState();

		// Reset pagination for new searches
		if (resetOffset) {
			offset = 0;
			allLoaded = false;
		}

		try {
			const url = new URL('/wp-json/ateso/v1/words', window.location.origin);
			url.searchParams.set('offset', offset);
			if (searchTerm && searchTerm.length >= 2) {
				url.searchParams.set('search', searchTerm);
			}

			const response = await fetch(url);
			if (!response.ok) throw new Error('Search failed');

			const data = await response.json();
			const words = data.words || [];

			// Clear existing cards for new searches
			if (resetOffset) {
				container.innerHTML = '';
			}

			if (words.length === 0) {
				if (resetOffset) {
					container.innerHTML = `<div class="no-results">No words found for "${searchTerm}"</div>`;
				}
				allLoaded = true;
				if (loadMoreBtn) {
					loadMoreBtn.style.display = 'none';
				}
			} else {
				words.forEach(word => {
					const card = createCard(word);
					container.appendChild(card);
				});

				offset += words.length;
				
				// Check if we've loaded all results
				if (words.length < 20) {
					allLoaded = true;
				}

				// Show/hide load more button
				if (loadMoreBtn) {
					if (allLoaded) {
						loadMoreBtn.style.display = 'none';
					} else {
						loadMoreBtn.style.display = 'inline-block';
						loadMoreBtn.textContent = 'Load More';
						loadMoreBtn.disabled = false;
					}
				}
			}
		} catch (err) {
			console.error('Search error:', err);
			if (resetOffset) {
				container.innerHTML = '<div class="search-error">Failed to search. Please try again.</div>';
			}
		} finally {
			isSearching = false;
			hideLoadingState();
		}
	};

	const loadMoreWords = async () => {
		if (isLoading || allLoaded) return;
		isLoading = true;
		
		// Update button state
		if (loadMoreBtn) {
			loadMoreBtn.textContent = 'Loading...';
			loadMoreBtn.disabled = true;
		}
		
		loading.style.display = 'block';

		try {
			const url = new URL('/wp-json/ateso/v1/words', window.location.origin);
			url.searchParams.set('offset', offset);
			if (currentSearch && currentSearch.length >= 2) {
				url.searchParams.set('search', currentSearch);
			}

			const response = await fetch(url);
			if (!response.ok) throw new Error('Failed to load');

			const data = await response.json();
			const words = data.words || [];

			if (words.length === 0) {
				allLoaded = true;
				if (loadMoreBtn) {
					loadMoreBtn.textContent = 'No more words';
					loadMoreBtn.style.display = 'none';
				}
				loading.textContent = 'No more words.';
				return;
			}

			words.forEach(word => {
				const card = createCard(word);
				container.appendChild(card);
			});

			offset += words.length;
			
			// Check if we've loaded all words
			if (words.length < 20) {
				allLoaded = true;
				if (loadMoreBtn) {
					loadMoreBtn.textContent = 'No more words';
					loadMoreBtn.style.display = 'none';
				}
			}
		} catch (err) {
			console.error(err);
			loading.textContent = 'Failed to load more words.';
			if (loadMoreBtn) {
				loadMoreBtn.textContent = 'Load More';
				loadMoreBtn.disabled = false;
			}
		} finally {
			isLoading = false;
			loading.style.display = 'none';
			
			// Reset button state if not all loaded
			if (!allLoaded && loadMoreBtn) {
				loadMoreBtn.textContent = 'Load More';
				loadMoreBtn.disabled = false;
			}
		}
	};

	// Debounced search function
	const debouncedSearch = (searchTerm) => {
		// Clear existing timeout
		if (searchTimeout) {
			clearTimeout(searchTimeout);
		}

		// Set new timeout
		searchTimeout = setTimeout(() => {
			currentSearch = searchTerm;
			searchWords(searchTerm, true);
		}, 300);
	};

	// Load More button click handler
	if (loadMoreBtn) {
		loadMoreBtn.addEventListener('click', loadMoreWords);
	}

	// Live search with debouncing
	searchInput?.addEventListener('input', function (e) {
		const searchTerm = e.target.value.trim();
		
		// Clear search and show all words if empty
		if (searchTerm === '') {
			currentSearch = '';
			offset = 20; // Reset to initial offset
			allLoaded = false;
			
			// Reload initial words
			searchWords('', true);
			
			// Show load more button if we had more than 20 words initially
			if (loadMoreBtn && initialCards.length >= 20) {
				loadMoreBtn.style.display = 'inline-block';
				loadMoreBtn.textContent = 'Load More';
				loadMoreBtn.disabled = false;
			}
			return;
		}

		// Only search if 2 or more characters
		if (searchTerm.length >= 2) {
			debouncedSearch(searchTerm);
		} else {
			// Clear results for short searches
			container.innerHTML = '<div class="search-hint">Type at least 2 characters to search...</div>';
			if (loadMoreBtn) {
				loadMoreBtn.style.display = 'none';
			}
		}
	});
});

document.addEventListener('DOMContentLoaded', function () {
	const copyButtons = document.querySelectorAll('.copy-link-button');

	copyButtons.forEach(button => {
		button.addEventListener('click', function () {
			const url = this.getAttribute('data-url');
			navigator.clipboard.writeText(url).then(() => {
				alert('Link copied to clipboard!');
			});
		});
	});
});
