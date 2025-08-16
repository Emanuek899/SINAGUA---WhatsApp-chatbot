# 🤖 Proyecto SINAGUA — Guía de Instalación y Uso

## 📦 Dependencias necesarias
- [XAMPP](https://www.apachefriends.org/es/index.html)
- [Node.js](https://nodejs.org/)
- [ngrok](https://ngrok.com/downloads/windows)
- [composer](https://getcomposer.org/Composer-Setup.exe)

---

## 📖 Guía de uso

### 1️⃣ Colocar el proyecto en XAMPP
Copiar los archivos dentro de un directorio llamado **`SINAGUA`**, despues moverlo al directorio `htdocs` de XAMPP.  
Ruta predeterminada:
```
C:\xampp\htdocs
```

---

### 2️⃣ Instalar dependencias del Frontend
Ir al directorio `Frontend`, abrir una terminal y ejecutar:
```bash
npm install
```

### Instalar dependencias del Backend
Ir al directorio `APU`, abrir una terminal y ejecutar:
```bash
composer install
```

---

### 3️⃣ Configurar ngrok
#### Se deben de tener dos tuneles de red en ngrok
1. Instalar ngrok desde la [página oficial](https://ngrok.com/downloads/windows) y agregarlo al **PATH**.
2. Editar el archivo:
```
C:\Users\{usuario}\AppData\Local\ngrok\ngrok.yml
```
> 💡 Reemplaza `{usuario}` por tu usuario de Windows.

3. Agregar la siguiente configuración:
```yaml
tunnels:
  xampp:
    addr: 80
    proto: http
  node:
    addr: 3000
    proto: http
```

---

### 4️⃣ Encender servicios en XAMPP
Asegúrate de que **Apache** y **MySQL** estén activos.  
Esto permite que el directorio `APU` sea visible.

---

### 5️⃣ Iniciar ngrok
En una terminal, ejecutar:
```bash
ngrok start --all
```

---

### 6️⃣ Actualizar URLs de ngrok

Modificar el archivo .env que esta en el directorio `Frontend` con las URLs generadas:

---

### 7️⃣ Iniciar servidor de Node.js
Desde el directorio `Frontend`, ejecutar:
```bash
node server.js
```

---

## ✅ ¡Listo!
Tu **chatbot** ahora estará corriendo exitosamente. 🚀
