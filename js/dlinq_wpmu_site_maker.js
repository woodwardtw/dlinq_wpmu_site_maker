window.onload = function() {
	const mainHeader = document.querySelector('.title-header');
	const headerContent = mainHeader.innerHTML;
	if (headerContent === 'Archives' && document.querySelector('.glossary')){
		mainHeader.innerHTML = 'Glossary';
	}

}