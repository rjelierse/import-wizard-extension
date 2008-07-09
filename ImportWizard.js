function toggleCheck (em, toggle)
{
	check = document.getElementById (toggle);
	check.checked = !check.checked;
	
	em.style.backgroundColor = (check.checked) ? '#80ff80' : 'transparent';
}