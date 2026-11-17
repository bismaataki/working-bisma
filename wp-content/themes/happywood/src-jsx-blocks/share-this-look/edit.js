
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<p className="hw-share-this-look-heading">Share This Look</p>

			<div className="hw-share-this-look-buttons">
				<button type="button" disabled aria-label="WhatsApp">
					<svg viewBox="0 0 24 24" width="20" height="20">
						<path fill="currentColor" d="M12 2a10 10 0 0 0-8.5 15.3L2 22l4.9-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4-1.1l-.3-.2-2.9.8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-5.9c-.2-.1-1.3-.6-1.5-.7-.2-.1-.4-.1-.5.1l-.7.8c-.1.2-.3.2-.5.1a6 6 0 0 1-1.8-1.1 7 7 0 0 1-1.2-1.5c-.1-.2 0-.3.1-.4l.4-.5c.1-.1.2-.3.2-.4l-.7-1.6c-.2-.4-.4-.4-.5-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.7.8-.7 1.8s.7 2.1.8 2.2a8 8 0 0 0 3.4 3c.4.2.8.3 1.1.4.5.1.9.1 1.3.1.4-.1 1.3-.6 1.5-1.1.2-.5.2-.9.1-1-.1-.1-.3-.2-.5-.3Z"/>
					</svg>
				</button>

				<button type="button" disabled aria-label="Facebook">
					<svg viewBox="0 0 24 24" width="20" height="20">
						<path fill="currentColor" d="M13.5 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.3 0-1.3-.1-2.4-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.4V13h2.8v8h3.3Z"/>
					</svg>
				</button>

				<button type="button" disabled aria-label="Copy Link">
					<svg viewBox="0 0 24 24" width="20" height="20">
						<path d="M10 13.5a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1.7 1.7M14 10.5a4 4 0 0 0-5.7 0l-3 3A4 4 0 0 0 11 19.2l1.7-1.7" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round"/>
					</svg>
				</button>
			</div>
		</div>
	);
}
