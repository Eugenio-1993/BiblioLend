# 📖 El Grimorio de Commits

En la Academia Mágica de Código , los magos ordenados no tiran los hechizos al azar. Llevan un registro impecable en sus bitácoras usando la técnica milenaria conocida como **Conventional Commits**.

## ¿Por qué usamos esta magia?
Si un compañero hechicero (o tú mismo en el futuro) lee el historial, debe entender exactamente qué hace cada commit sin necesidad de leer todo el código. Un historial limpio previene desastres y maldiciones.

## 🪄 La Estructura del Hechizo

Cada mensaje de commit debe seguir esta fórmula mágica:

```text
<tipo>: <descripción corta y en minúsculas del cambio>
```

### Tipos de Magia Permitidos (Tipos de Commit)

Usa el tipo adecuado según lo que hayas hecho:

*   ✨ `feat`: (Feature). Usado cuando agregas una nueva funcionalidad o completas un nuevo reto.
    *   *Ejemplo:* `feat: resolver reto 1 de iniciacion`
*   🐛 `fix`: (Fix). Usado cuando reparas una maldición (bug) en un código que ya existía pero no funcionaba bien.
    *   *Ejemplo:* `fix: corregir error matematico en reto 2`
*   📚 `docs`: (Documentation). Usado cuando solo cambias archivos de texto, el `README.md` o agregas comentarios explicativos.
    *   *Ejemplo:* `docs: responder preguntas del reto 10`
*   🎨 `style`: (Style). Usado cuando cambias el formato del código (espacios, puntos y comas) sin afectar la lógica.
*   ♻️ `refactor`: (Refactoring). Usado cuando mejoras cómo está escrito un hechizo, pero hace exactamente lo mismo.
*   🧪 `test`: (Test). Usado cuando agregas o corriges pruebas mágicas (Unit tests).
*   🔧 `chore`: (Chore). Usado para tareas de mantenimiento, como actualizar dependencias.

---

**❌ Ejemplos de Malos Hechizos (NO hacer esto):**
*   `hice un cambio`
*   `reto 3 listo`
*   `agregue ifs`
*   `fix bug`

**✅ Ejemplos de Buenos Hechizos:**
*   `feat: completar reto 3 con ciclos for`
*   `fix: ajustar retorno nulo en metodo aritmanciaBasica`
*   `docs: agregar respuestas sobre el uso de IA`
