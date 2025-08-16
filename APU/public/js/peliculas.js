async function mostrarPeliculas(){
	const padre = document.getElementById("nombrePelicula");
	padre.innerHTML = "";
	try {
		const response = await fetch("http://localhost/APU/ajax/Peliculas.php")
		if(!response.ok) throw new Error("Error en la respuesta");

		const peliculas = await response.json();
		peliculas.forEach(pelicula => {
			const p = document.createElement("p");
			const delBtn = document.createElement("button");
			delBtn.setAttribute("data-id", pelicula.id);
			p.textContent = `Titulo pelicula: ${pelicula.nombre}`;
			delBtn.textContent = "Borrar pelicula";
			delBtn.addEventListener("click", () => borrarPelicula(pelicula.id))
			padre.appendChild(p);
			padre.appendChild(delBtn);
		});
		
	} catch(error) {
		console.error({"message": error});
	}
}

async function borrarPelicula(id){
	try {
		const response = await fetch(`http://localhost/APU/ajax/Peliculas.php?id=${id}`, {
			method: 'DELETE',
			headers: {
				"Content-Type": "application/json"
			}
		});
		if (!response.ok) throw new Error("Error borrando la pelicula")
		setTimeout(() => mostrarPeliculas(), 2000);
	} catch (error) {
		console.error({"message": error})
	}
}

async function añadirPelicula(){
	try{
		const nombre = document.getElementById("nombrePeliculaInput").value;
		const datos = {
			"nombre": nombre
		};
		const response = await fetch("http://localhost/APU/ajax/Peliculas.php", {
			method: "POST",
			headers: {
				"Content-Type": "application/json"
			},
			body: JSON.stringify(datos)
		});
		if (!response.ok) throw new Error("Error creando la pelicula");
		setTimeout(() => mostrarPeliculas(), 2000);
	} catch (error) {
		console.error({"message": error})
	}
}

document.addEventListener("DOMContentLoaded", async() => {
	const btn = document.getElementById("peliculasBtn");
	const subirPeliculaBtn = document.getElementById("subirPeliculaBtn");
	subirPeliculaBtn.addEventListener("click", () => añadirPelicula())
	btn.addEventListener("click", () => mostrarPeliculas());
});