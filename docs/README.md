# Sistema de Diseño: Tactile Enterprise Asset Control (SIGA Enterprise)

> **Documento de Referencia de Estilos y Arquitectura Visual**  
> *Basado en las especificaciones y prototipos ubicados en `docs/stitch_asset_loan_maintenance_system`.*

---

## 1. Filosofía y Concepto Visual

El sistema de diseño **Tactile Enterprise Asset Control** (creado para la suite **SIGA Enterprise**) establece un lenguaje visual de grado institucional, autoritario, táctil y de alta densidad de información, concebido específicamente para:

- **Control y trazabilidad de activos de hardware** (laptops, equipo de laboratorio, proyectores, herramientas técnicas).
- **Flujos de préstamo y devolución con tiempos de entrega críticos**.
- **Mesa de ayuda y ticketing de soporte técnico / mantenimiento correctivo y preventivo**.
- **Bitácoras forenses de auditoría y movimientos en tiempo real**.

### Pilares Fundamentales

| Pilar | Principio de Diseño |
| :--- | :--- |
| **Neumorfismo Funcional Moderno** | A diferencia del neumorfismo decorativo clásico (frecuentemente criticado por falta de contraste), este sistema utiliza **planos extruidos de doble tono**, **superficies fresadas/rehundidas (*inset debossed*)** y **micro-bordes de definición** que garantizan contraste de grado **WCAG AAA**. Evoca consolas físicas de ingeniería y hardware industrial. |
| **Precisión de Instrumentación** | Cuadrículas estrictas basadas en ritmo de 8 puntos, alineaciones rígidas y alta densidad de datos sin saturar al usuario bajo estrés operativo. |
| **Retroalimentación Háptica / Táctil** | Los controles interactivos simulan físicamente botones mecánicos de consola: elevación natural en reposo, elevación dinámica en `:hover` y compresión con sombra interior en `:active` (`scale(0.98)`). |
| **Trazabilidad Inequívoca de Estados** | Códigos de color semánticos de alto contraste para activos disponibles, en préstamo, en reparación o dados de baja lógica. |

---

## 2. Paleta Cromática y Design Tokens

El sistema cromático está anclado en tonos azul naval profundo (*Deep Space Navy*), acentos ámbar cálido (*Honey Bronze* / *Vanilla Custard*) y alertas carmesí (*Brown Red* / *Bordeaux*).

### 2.1 Tokens Principales del Sistema

| Token | Código HEX | Rol Semántico / Uso |
| :--- | :--- | :--- |
| `primary` | `#001A2B` | Azul Comando Oscuro / Fondos principales en modo oscuro / Encabezados dominantes. |
| `primary-container` | `#003049` | Azul Institucional / Botones primarios / Barras superiores. |
| `on-primary` | `#FFFFFF` | Texto blanco puro sobre superficies primarias. |
| `on-primary-container`| `#7398B6` | Texto y glifos secundarios sobre azul institucional. |
| `secondary` | `#835500` / `#E09F3E` | Ámbar / Honey Bronze para focos, advertencias activas y pestañas interactivas. |
| `secondary-container`| `#FEB956` | Fondo para chips de advertencia y selecciones secundarias. |
| `tertiary` | `#675F2B` | Tono oliva / verde musgo para elementos técnicos auxiliares. |
| `error` | `#BA1A1A` / `#9E2A2B` | Carmesí crítico para fallas de hardware, tickets urgentes y bajas lógicas. |
| `error-container` | `#FFDAD6` | Fondo suave de alerta para registros eliminados o fallas críticas. |
| `surface` | `#FFF8F7` | Superficie base limpia con sutil calidez slate. |
| `surface-container-lowest` | `#FFFFFF` | Blanco puro para tarjetas elevadas (*raised cards*) en modo claro. |
| `surface-container-low` | `#FFF0EF` | Fondo para campos de entrada rehundidos (*inset*). |
| `surface-container` | `#FFE9E7` | Separadores y contenedores intermedios. |
| `on-surface` | `#410004` | Texto principal de ultra-alto contraste en modo claro. |
| `on-surface-variant` | `#42474d` | Texto secundario y etiquetas descriptivas. |
| `outline` | `#72787E` | Bordes de control e iconos neutrales. |
| `outline-variant` | `#C2C7CD` | Bordes sutiles y divisores de tablas. |

---

### 2.2 Arquitectura Light vs Dark Mode

```
┌─────────────────────────────────┬─────────────────────────────────┐
│           LIGHT MODE            │            DARK MODE            │
├─────────────────────────────────┼─────────────────────────────────┤
│ Canvas:      #F0F4F8 / #FFF8F7  │ Canvas:      #001A29 / #001A2B  │
│ Tarjeta:     #FFFFFF / #F8FAFC  │ Tarjeta:     #00253A            │
│ Rehundido:   #FFF0EF / #E4EBF2  │ Rehundido:   #00121E / #00131E  │
│ Primario:    #003049            │ Resplandor:  #E09F3E            │
│ Texto Base:  #410004 / #1E293B  │ Texto Base:  #FFF8F7 / #CAE6FF  │
└─────────────────────────────────┴─────────────────────────────────┘
```

---

### 2.3 Mapeo de Estados Operativos (Status Badges)

Cada estado operativo del inventario cuenta con una tríada estricta de **Fondo (Fill)**, **Texto** y **Borde**:

| Estado | Fondo (Light) | Texto (Light) | Borde (Light) | Equivalente Dark | Elemento Visual Distintivo |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Activo (Disponible)** | `#E6F4EA` | `#137333` | `#34A853` | Fill: `#0D2818` / Text: `#81C995` | Punto verde pulsante de 6px (`.animate-live-dot`). |
| **En Préstamo** | `#FFF8E1` | `#B06000` | `#E09F3E` | Fill: `#2D2006` / Text: `#FDD663` | Contador regresivo de vencimiento y fecha límite. |
| **En Reparación** | `#FCE8E6` | `#9E2A2B` | `#EA4335` | Fill: `#311012` / Text: `#F28B82` | Nivel de criticidad del ticket (`#TK-XXXX`). |
| **Baja Lógica** | `#EAECEF` | `#4A5568` | `#9AA0A6` | Fill: `#1A232E` / Text: `#9AA0A6` | Código de serie con texto tachado (`.serial-strike`). |

---

## 3. Tipografía y Jerarquía

El sistema utiliza tres familias de Google Fonts con funciones no intercambiables:

```
┌───────────────────────┬────────────────────────────────────────────────────────┐
│ Familia               │ Rol en la Aplicación                                   │
├───────────────────────┼────────────────────────────────────────────────────────┤
│ Space Grotesk         │ Títulos, KPIs, módulos, modales, autoridad técnica.    │
│ Hanken Grotesk        │ Texto de lectura, párrafos, formularios, descripciones.│
│ JetBrains Mono        │ Números de serie, IDs de tickets, atajos (Ctrl+K), UTC │
└───────────────────────┴────────────────────────────────────────────────────────┘
```

### Escala Tipográfica Oficial

| Token | Fuente | Tamaño | Peso | Line Height | Letter Spacing | Uso Típico |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `headline-xl` | Space Grotesk | `3.000rem` (48px) | 700 (Bold) | 1.15 | `-0.03em` | Portada institucional, métricas hero. |
| `headline-lg` | Space Grotesk | `2.250rem` (36px) | 600 (Semibold) | 1.20 | `-0.02em` | Títulos de módulos y secciones. |
| `headline-md` | Space Grotesk | `1.500rem` (24px) | 600 (Semibold) | 1.30 | `-0.01em` | Encabezados de paneles y tarjetas. |
| `headline-sm` | Space Grotesk | `1.250rem` (20px) | 600 (Semibold) | 1.40 | `0` | Subtítulos y modales. |
| `body-lg` | Hanken Grotesk | `1.125rem` (18px) | 400 (Regular) | 1.55 | `-0.01em` | Textos introductorios. |
| `body-md` | Hanken Grotesk | `0.938rem` (15px) | 400 (Regular) | 1.50 | `0` | Cuerpo de texto base, celdas de tablas. |
| `body-sm` | Hanken Grotesk | `0.813rem` (13px) | 400 (Regular) | 1.45 | `+0.005em` | Notas al pie, descripciones secundarias. |
| `label-lg` | JetBrains Mono | `0.875rem` (14px) | 500 (Medium) | 1.30 | `+0.02em` | Cabeceras de tablas de datos. |
| `label-md` | JetBrains Mono | `0.750rem` (12px) | 500 (Medium) | 1.30 | `+0.04em` | Series de activos (`#AST-9823-X`). |
| `label-sm` | JetBrains Mono | `0.688rem` (11px) | 600 (Semibold) | 1.20 | `+0.06em` | Insignias de versión, atajos `Ctrl + K`. |

---

## 4. Elevación y Modelo Neumórfico Dual-Light (315°)

Para lograr volumen tridimensional tangible sin perder accesibilidad, se proyecta una fuente de luz virtual orientada a **315° (Superior-Izquierda)**:

### 4.1 Clases CSS de Elevación y Profundidad

#### 1. Superficie Extruida / Elevada (`.neu-raised`)
Utilizada en tarjetas de módulos, métricas, paneles y tarjetas de activos:
```css
.neu-raised {
  background: #FFF8F7;
  box-shadow: -6px -6px 14px rgba(255, 255, 255, 0.95), 
               6px  6px 16px rgba(0, 48, 73, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.7);
}

.dark .neu-raised {
  background: #001A2B;
  box-shadow: -4px -4px 12px rgba(255, 243, 176, 0.04), 
               6px  6px 18px rgba(0, 10, 18, 0.75);
  border: 1px solid rgba(224, 159, 62, 0.15);
}
```

#### 2. Superficie Rehundida / Cavidad (`.neu-inset`)
Utilizada en campos de búsqueda, inputs de formulario, selects y controles inactivos:
```css
.neu-inset {
  background: #FFF0EF;
  box-shadow: inset  3px  3px 6px rgba(0, 48, 73, 0.08), 
              inset -3px -3px 6px rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(0, 48, 73, 0.06);
}

.dark .neu-inset {
  background: #001524;
  box-shadow: inset  3px  3px 6px rgba(0, 10, 18, 0.85), 
              inset -2px -2px 5px rgba(255, 243, 176, 0.03);
  border: 1px solid rgba(0, 0, 0, 0.6);
}
```

#### 3. Botón Táctil Mecánico Primario (`.neu-btn-primary`)
```css
.neu-btn-primary {
  background: #003049;
  color: #FFFFFF;
  box-shadow: -3px -3px 8px rgba(255, 255, 255, 0.6), 
               4px  5px 12px rgba(0, 48, 73, 0.25);
  border: 1px solid rgba(255, 243, 176, 0.2);
  transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.neu-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: -4px -4px 12px rgba(255, 255, 255, 0.8), 
               6px  8px 16px rgba(0, 48, 73, 0.32);
}

.neu-btn-primary:active {
  transform: scale(0.98);
  box-shadow: inset  3px  3px 6px rgba(0, 10, 20, 0.6), 
              inset -2px -2px 5px rgba(255, 243, 176, 0.2);
}
```

#### 4. Menús y Modales Flotantes (`.neu-floating`)
```css
.neu-floating {
  box-shadow: 0 20px 35px -10px rgba(0, 48, 73, 0.18), 
              0  1px  4px rgba(0, 48, 73, 0.06);
}

.dark .neu-floating {
  box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.85);
  border: 1px solid rgba(224, 159, 62, 0.2);
}
```

---

## 5. Geometría, Bordes y Espaciado

### 5.1 Radios de Esquina (*Border Radius*)

- `rounded-2xl` (`1.5rem` / `24px`): Contenedores macro, tarjetas de activos, paneles principales y modales. Capturan la sombra dual suavemente sin aristas duras.
- `rounded-xl` (`0.75rem` a `1rem` / `12px` a `16px`): Botones de acción, inputs de texto, buscadores y tarjetas anidadas.
- `rounded-lg` (`0.5rem` / `8px`): Celdas destacadas, tags internos y pestañas.
- `rounded-full` (`9999px`): Badges de estado (*Activo*, *En Préstamo*, etc.), avatares e indicadores de pulso.

### 5.2 Sistema de Espaciado (Ritmo de 8 Puntos)

```
space-xs : 0.25rem (4px)   ──  Micro-separadores, tags compactos
space-sm : 0.50rem (8px)   ──  Espaciado interno de botones, badges
space-md : 1.00rem (16px)  ──  Padding estándar de inputs y celdas
space-lg : 1.50rem (24px)  ──  Padding de tarjetas, gutters de cuadrícula
space-xl : 2.50rem (40px)  ──  Separación entre bloques mayores
```

---

## 6. Componentes Clave de UI

### 6.1 Barra Superior Institucional (TopNavBar)
- Posición fija (`sticky top-0 z-40`) con altura `h-16`.
- Botón táctil para el cajón lateral (*Hamburger Drawer Trigger*).
- Título institucional en Space Grotesk (`SIGA Enterprise`) con etiqueta de versión en JetBrains Mono (`v4.2 PRO`).
- Buscador predictivo rápido con atajo de teclado accesible (`Ctrl + K`).

### 6.2 Buscador Predictivo Inteligente (`Ctrl + K`)
- Campo rehundido debossed (`neu-inset`) con icono de lupa en Honey Bronze (`#E09F3E`).
- Despliega menú flotante (`.neu-floating`) con resultados agrupados por categorías:
  - 🖥️ **Activos** (con serie `#AST-...`).
  - 📋 **Préstamos** (con usuario y fecha de devolución).
  - 🔧 **Tickets de Servicio** (con criticidad e ID `#TK-...`).
  - 👤 **Técnicos y Usuarios**.

### 6.3 Tablas de Datos de Alta Densidad
- Cabecera fija (`sticky top-0`) con tipografía `label-md` en JetBrains Mono y fondo sutilmente contrastado.
- Filas alternadas con micro-interacción al pasar el cursor (`hover:bg-surface-container-low`).
- Fila seleccionada con sombra de presión debossed.
- Soporte visual de **Baja Lógica / Soft Delete**:
  ```css
  .row-soft-deleted {
    background-color: #fff0ef !important;
    opacity: 0.78;
  }
  .row-soft-deleted .serial-strike {
    text-decoration: line-through;
    color: #9E2A2B;
  }
  ```

### 6.4 Tarjetas Interactivas de Activo / Ticket
- Efecto `:hover` con elevación fluida:
  ```css
  .neu-card-interactive {
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .neu-card-interactive:hover {
    transform: translateY(-2px);
    box-shadow: -8px -8px 18px rgba(255, 255, 255, 1), 
                 8px  8px 20px rgba(0, 48, 73, 0.12);
  }
  ```

### 6.5 Indicador de Eventos en Tiempo Real (Live Pulse)
```css
@keyframes live-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.15); }
}
.animate-live-dot {
  animation: live-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

---

## 7. Inventario de Prototipos Incluidos en `docs/`

La carpeta `docs/stitch_asset_loan_maintenance_system/` contiene la suite completa de pantallas prototipo funcionales en HTML + Tailwind CSS. A continuación se detalla cada módulo:

| Carpeta del Prototipo | Archivo | Descripción del Módulo |
| :--- | :--- | :--- |
| `tactile_enterprise_asset_control/` | `DESIGN.md` | Especificación técnica original del sistema de diseño (Design Tokens YAML + Guía de Estilo). |
| `dashboard_principal_neum_rfico/` | `code.html` | Panel principal con KPIs de inventario, equipos prestados, cola de mantenimiento y telemetría. |
| `portal_p_blico_y_cat_logo_de_equipos/` | `code.html` | Portal web público con catálogo filtrable de equipos disponibles para préstamo estudiantil/docente. |
| `control_de_activos_y_pr_stamos_crud/` | `code.html` | Gestión CRUD de activos: altas con seriales, códigos de barra, categorías y modales de edición. |
| `control_detallado_de_pr_stamos_y_devoluciones/` | `code.html` | Registro de préstamos activos, fechas límite, checklist de condiciones de entrega y alertas de retraso. |
| `tickets_y_servicios_de_mantenimiento/` | `code.html` | Sistema de tickets de servicio técnico: asignación a técnicos, diagnósticos, repuestos y prioridades. |
| `administraci_n_de_usuarios_roles_y_permisos/` | `code.html` | Gestión de usuarios, asignación de roles (Admin, Técnico, Solicitante) y permisos granulares. |
| `bit_cora_y_auditor_a_de_movimientos_en_tiempo_real/` | `code.html` | Registro cronológico en vivo de acciones, préstamos y modificaciones vía WebSockets. |
| `bit_cora_con_modal_de_exportaci_n_y_reporte_forense_avanzado/` | `code.html` | Auditoría avanzada con modal de exportación (CSV, PDF, JSON) y sellos de verificación criptográfica. |
| `inicio_de_sesi_n_y_recuperaci_n_con_token/` | `code.html` | Pantallas de autenticación: Login corporativo y recuperación de contraseña con token 2FA táctil. |
| `siga_enterprise_asset_loan_management/` | `code.html` | Prototipo integrado de navegación global del ecosistema SIGA Enterprise. |

---

## 8. Guía de Integración Rápida en Laravel (Blade + Tailwind CSS)

### Paso 1: Importar Tipografías e Iconos en Blade (`layout.blade.php`)
```html
<!-- Google Fonts: Space Grotesk, Hanken Grotesk, JetBrains Mono -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300..900;1,300..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

<!-- Material Symbols Outlined -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
```

### Paso 2: Configuración en `resources/css/app.css` (Tailwind v4)
```css
@import 'tailwindcss';

@theme {
  /* Tipografías */
  --font-headline: 'Space Grotesk', sans-serif;
  --font-body: 'Hanken Grotesk', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;

  /* Paleta Tactile Enterprise */
  --color-primary: #001A2B;
  --color-primary-container: #003049;
  --color-secondary: #835500;
  --color-secondary-container: #FEB956;
  --color-surface: #FFF8F7;
  --color-surface-container-low: #FFF0EF;
  --color-surface-container-lowest: #FFFFFF;
  --color-on-surface: #410004;
  --color-on-surface-variant: #42474D;
  --color-error: #BA1A1A;
  --color-outline: #72787E;
  --color-outline-variant: #C2C7CD;
}

/* Utilidades Neumórficas */
.neu-raised {
  background: #FFF8F7;
  box-shadow: -6px -6px 14px rgba(255, 255, 255, 0.95), 6px 6px 16px rgba(0, 48, 73, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.7);
}

.neu-inset {
  background: #FFF0EF;
  box-shadow: inset 3px 3px 6px rgba(0, 48, 73, 0.08), inset -3px -3px 6px rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(0, 48, 73, 0.06);
}

.neu-btn-primary {
  background: #003049;
  color: #FFFFFF;
  box-shadow: -3px -3px 8px rgba(255, 255, 255, 0.6), 4px 5px 12px rgba(0, 48, 73, 0.25);
  border: 1px solid rgba(255, 243, 176, 0.2);
  transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}
.neu-btn-primary:active {
  transform: scale(0.98);
  box-shadow: inset 3px 3px 6px rgba(0, 10, 20, 0.6), inset -2px -2px 5px rgba(255, 243, 176, 0.2);
}

.neu-floating {
  box-shadow: 0 20px 35px -10px rgba(0, 48, 73, 0.18), 0 1px 4px rgba(0, 48, 73, 0.06);
}
```

---

*Para dudas o ampliación de especificaciones de diseño, consultar [`DESIGN.md`](./stitch_asset_loan_maintenance_system/tactile_enterprise_asset_control/DESIGN.md).*
