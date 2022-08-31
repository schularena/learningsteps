function getWordCount (str) {
	return str.split(/\s+/).length;
}

function trimSpaces (str) {
	return str.replace(/(^\s*)|(\s*$)/gi, '');
}

function ReadMore (element, options, blocs) {
	const settings = {
		originalContentArr: [],
		truncatedContentArr: [],
	};

	function init () {
		for (let i = 0; i < element.length; i++) {
			truncate(element[i], i);
		}
	}
  
	function destroy () {
		for (let i = 0; i < element.length; i++) {
			const id = element[i].getAttribute('data-bloc');
			let originalContent;
			blocs.forEach(function (bloc) {
				if (bloc.id === id) {
					originalContent = bloc.texte;
				}
			});
			element[i].innerHTML = originalContent;
			const link = document.querySelector(`#${options.blockClassName}_${i}`);
			if (link) {
				link.removeEventListener('click', handleLinkClick, false);
			}
		}
		const balises = document.querySelectorAll(`.${options.blockClassName}_bouton`);
		balises.forEach(function (balise) {
			if (balise.parentNode !== null) {
				balise.parentNode.removeChild(balise);
			}
		});
		settings.originalContentArr = [];
		settings.truncatedContentArr = [];
	}

	function ellipse (str, max) {
		const trimedSpaces = trimSpaces(str);
		return trimedSpaces.split(/\s+/).slice(0, max).join(' ') + '…';
	}

	function truncate (el, idx) {
		const numberWords = options.wordsCount;
		const id = el.getAttribute('data-bloc');
		let originalContent;
		blocs.forEach(function (bloc) {
			if (bloc.id === id) {
				originalContent = bloc.texte;
			}
		});
		const truncateContent = ellipse(originalContent, numberWords);
		const div = document.createElement('div');
		div.innerHTML = originalContent;
		const originalContentCount = getWordCount(div.textContent);
		settings.originalContentArr.push(originalContent);
		settings.truncatedContentArr.push(truncateContent);

		if (numberWords < originalContentCount) {
			el.innerHTML = truncateContent;
			createLink(idx);
		}
	}

	function createLink (idx) {
		const linkWrap = document.createElement('span');
		linkWrap.className = `${options.blockClassName}_bouton`;
		linkWrap.innerHTML = `<a id=${options.blockClassName}_${idx} style="cursor: pointer;">${options.moreText}</a>`;

		element[idx].after(linkWrap);

		const link = document.querySelector(`#${options.blockClassName}_${idx}`);
		link.addEventListener('click', handleLinkClick, false);
		link.idx = idx;
	}

	function handleLinkClick (e) {
		const idx = e.currentTarget.idx;
		const target = e.currentTarget;
		if (target.dataset.clicked !== 'true') {
			element[idx].innerHTML = settings.originalContentArr[idx];
			target.innerHTML = options.lessText;
			target.dataset.clicked = 'true';
		} else {
			element[idx].innerHTML = settings.truncatedContentArr[idx];
			target.innerHTML = options.moreText;
			target.dataset.clicked = 'false';
		}
	}

	return {
		init: init,
		destroy: destroy
	};
}

export default ReadMore;
