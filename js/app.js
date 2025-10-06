function toggleCategorias() {
	const lista = document.getElementById("categoriasLista");
	if (lista.style.display === "none") {
		lista.style.display = "block";
	} else {
		lista.style.display = "none";
	}
}
// Inicialmente mostrar (o podrías dejarlo oculto con display:none)
window.onload = function () {
	document.getElementById("categoriasLista").style.display = "none";
};

function toggleCategorias() {
	const lista = document.getElementById("categoriasLista");
	lista.style.display = lista.style.display === "none" ? "block" : "none";
}

function toggleCarreras() {
	const lista = document.getElementById("carrerasLista");
	lista.style.display = lista.style.display === "none" ? "block" : "none";
}

window.onload = function () {
	document.getElementById("categoriasLista").style.display = "none";
	document.getElementById("carrerasLista").style.display = "none";
};
