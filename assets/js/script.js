document.addEventListener('DOMContentLoaded', function () {
	const container = document.querySelector('.ateso-words-archive');
	const loading = document.querySelector('.ateso-words-loading');
	const searchInput = document.querySelector('.ateso-words-search');
	let isLoading = false;

	// Live search
	searchInput?.addEventListener('input', function (e) {
		const term = e.target.value.toLowerCase();
		const cards = document.querySelectorAll('.ateso-word-card');

		cards.forEach(card => {
			const title = card.dataset.title.toLowerCase();
			card.style.display = title.includes(term) ? 'block' : 'none';
		});
	});

	// Infinite scroll
	const loadMoreWords = async () => {
		if (isLoading) return;
		isLoading = true;
		loading.style.display = 'block';

		const offset = parseInt(container.dataset.offset) || 0;

		try {
			const res = await fetch(`/wp-json/ateso/v1/words?offset=${offset}`);
			const words = await res.json();

			if (words.length) {
				words.forEach(word => {
					const div = document.createElement('div');
					div.className = 'ateso-word-card';
					div.dataset.title = word.title;
					div.innerHTML = `<h3>${word.title}</h3>`;
					container.appendChild(div);
				});
				container.dataset.offset = offset + words.length;
			} else {
				window.removeEventListener('scroll', scrollHandler);
				loading.textContent = 'No more words.';
			}
		} catch (e) {
			console.error('Error loading words', e);
		} finally {
			isLoading = false;
			loading.style.display = 'none';
		}
	};

	const scrollHandler = () => {
		const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
		if (scrollTop + clientHeight >= scrollHeight - 100) {
			loadMoreWords();
		}
	};

	window.addEventListener('scroll', scrollHandler);
});
