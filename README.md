# Menu Icons Pro

Un plugin de WordPress para añadir iconos personalizados a los elementos del menú con una interfaz fácil de usar.

## Características

- **Múltiples tipos de iconos**: Soporte para Dashicons (iconos de WordPress), Font Awesome e iconos personalizados
- **Interfaz intuitiva**: Campos integrados en el editor de menús de WordPress
- **Vista previa en tiempo real**: Ve como lucirán los iconos antes de guardar
- **Selector de iconos**: Selector visual para Dashicons y Font Awesome
- **Posicionamiento flexible**: Coloca iconos antes, después o en lugar del texto
- **Tamaño personalizable**: Ajusta el tamaño de los iconos según tus necesidades
- **Responsive**: Los iconos se adaptan a diferentes tamaños de pantalla
- **Compatible**: Funciona con la mayoría de temas de WordPress

## Instalación

1. Sube la carpeta `menu-icons-pro` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el panel de administración de WordPress
3. Ve a **Apariencia > Menús** para empezar a añadir iconos

## Uso

### Añadir iconos a elementos del menú

1. Ve a **Apariencia > Menús** en tu panel de administración
2. Selecciona el menú que quieres editar
3. Haz clic en cualquier elemento del menú para expandir sus opciones
4. Verás una nueva sección llamada **"Configuración de Icono"**
5. Configura las siguientes opciones:

#### Tipo de Icono
- **Sin icono**: No mostrar ningún icono
- **Dashicons (WordPress)**: Usa los iconos integrados de WordPress
- **Font Awesome**: Usa iconos de Font Awesome
- **Icono personalizado**: Usa una imagen personalizada (URL)

#### Clase/URL del Icono
Dependiendo del tipo seleccionado:
- **Dashicons**: `dashicons-admin-home`, `dashicons-cart`, etc.
- **Font Awesome**: `fas fa-home`, `fab fa-facebook`, etc.
- **Personalizado**: URL completa de la imagen

#### Posición del Icono
- **Antes del texto**: El icono aparece antes del texto del menú
- **Después del texto**: El icono aparece después del texto del menú
- **Solo icono (sin texto)**: Solo se muestra el icono, sin texto

#### Tamaño del Icono
Establece el tamaño en píxeles (8-64px)

### Ejemplos de uso

#### Dashicons
```
Tipo: Dashicons (WordPress)
Valor: dashicons-admin-home
```

#### Font Awesome
```
Tipo: Font Awesome
Valor: fas fa-home
```

#### Icono personalizado
```
Tipo: Icono personalizado
Valor: https://tudominio.com/icono.png
```

## Iconos disponibles

### Dashicons más comunes
- `dashicons-admin-home` - Casa
- `dashicons-admin-users` - Usuarios
- `dashicons-admin-comments` - Comentarios
- `dashicons-admin-tools` - Herramientas
- `dashicons-cart` - Carrito
- `dashicons-camera` - Cámara
- `dashicons-calendar` - Calendario
- `dashicons-email` - Email
- `dashicons-phone` - Teléfono
- `dashicons-location` - Ubicación

### Font Awesome más comunes
- `fas fa-home` - Casa
- `fas fa-user` - Usuario
- `fas fa-envelope` - Sobre
- `fas fa-phone` - Teléfono
- `fas fa-shopping-cart` - Carrito
- `fas fa-camera` - Cámara
- `fas fa-calendar` - Calendario
- `fas fa-star` - Estrella
- `fas fa-heart` - Corazón
- `fas fa-search` - Buscar

## Personalización CSS

Puedes personalizar la apariencia de los iconos añadiendo CSS personalizado:

```css
/* Cambiar color de todos los iconos del menú */
.menu-icon {
    color: #ff6b6b;
}

/* Añadir efectos hover */
.menu-item:hover .menu-icon {
    transform: scale(1.2);
    color: #4ecdc4;
}

/* Estilos específicos para iconos personalizados */
.menu-icon.custom-icon {
    border-radius: 50%;
    border: 2px solid #fff;
}
```

## Compatibilidad

- WordPress 5.0+
- PHP 7.4+
- Funciona con la mayoría de temas de WordPress
- Compatible con temas responsive

## Soporte

Si encuentras algún problema o tienes sugerencias:

1. Verifica que tu tema es compatible
2. Asegúrate de que no hay conflictos con otros plugins
3. Comprueba la consola del navegador en busca de errores JavaScript

## Changelog

### Versión 1.0.0
- Lanzamiento inicial
- Soporte para Dashicons, Font Awesome e iconos personalizados
- Selector visual de iconos
- Vista previa en tiempo real
- Configuración de posición y tamaño

## Créditos

- Dashicons: Iconos oficiales de WordPress
- Font Awesome: Biblioteca de iconos gratuita
- Desarrollado con las mejores prácticas de WordPress
