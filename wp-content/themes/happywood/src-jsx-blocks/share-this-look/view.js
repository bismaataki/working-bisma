
document.addEventListener('click', async (event) => {
	const button = event.target.closest('.hw-share-copy');

	if (!button) {
		return;
	}

	const container = button.closest('.hw-share-this-look');
	const status = container?.querySelector('.hw-share-copy-status');
	const url = button.dataset.copyUrl;

	if (!url || !status) {
		return;
	}

	try {
		await navigator.clipboard.writeText(url);
		status.textContent = 'Link copied';

		window.setTimeout(() => {
			status.textContent = '';
		}, 2000);
	} catch (error) {
		status.textContent = 'Unable to copy link';
	}
});
