document.addEventListener('DOMContentLoaded', function () {
	const container = document.querySelector('.ateso-words-archive');
	const loading = document.querySelector('.ateso-words-loading');
	const searchInput = document.querySelector('.ateso-words-search');

	let isLoading = false;
	let offset = 20;
	let allLoaded = false;

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

	const loadMoreWords = async () => {
		if (isLoading || allLoaded) return;
		isLoading = true;
		loading.style.display = 'block';

		try {
			const response = await fetch(`/wp-json/ateso/v1/words?offset=${offset}`);
			if (!response.ok) throw new Error('Failed to load');

			const words = await response.json();

			if (!words.length) {
				allLoaded = true;
				loading.textContent = 'No more words.';
				return;
			}

			words.forEach(word => {
				const card = createCard(word);
				container.appendChild(card);
			});

			offset += words.length;
		} catch (err) {
			console.error(err);
			loading.textContent = 'Failed to load more words.';
		} finally {
			isLoading = false;
			loading.style.display = 'none';
		}
	};

	const handleScroll = () => {
		const nearBottom =
			window.innerHeight + window.scrollY >= document.body.offsetHeight - 100;

		if (nearBottom) {
			loadMoreWords();
		}
	};

	window.addEventListener('scroll', handleScroll);

	// Live search (title + meaning)
	searchInput?.addEventListener('input', function (e) {
		const term = e.target.value.toLowerCase();
		const cards = document.querySelectorAll('.ateso-word-card');

		cards.forEach(card => {
			const title = card.dataset.title;
			const meaning = card.dataset.meaning || '';
			card.style.display = (title.includes(term) || meaning.includes(term)) ? 'block' : 'none';
		});
	});
});
