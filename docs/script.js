document.addEventListener('DOMContentLoaded', function() {
    // Configurar Mermaid JS
    mermaid.initialize({
        startOnLoad: true,
        theme: 'default',
        securityLevel: 'loose',
        themeVariables: {
            primaryColor: '#3498db',
            primaryTextColor: '#fff',
            primaryBorderColor: '#2c3e50',
            lineColor: '#2c3e50',
            secondaryColor: '#6c757d',
            tertiaryColor: '#f8f9fa'
        },
        flowchart: {
            curve: 'basis'
        },
        htmlLabels: true
    });

    // Forzar la inicialización de Mermaid para asegurar que renderice todos los diagramas
    try {
        mermaid.init(undefined, '.mermaid');
    } catch (e) {
        console.error('Error al inicializar Mermaid:', e);
    }

    // Inicializar highlight.js para resaltar la sintaxis del código
    hljs.highlightAll();

    document.getElementById('printButton').addEventListener('click', function() {
        window.print();
    });

    // Funcionalidad para el sidebar
    const sidebar = document.getElementById('sidebar');
    const toggleSidebarBtn = document.getElementById('toggleSidebar');

    // Toggle sidebar en móviles
    toggleSidebarBtn.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });

    // Cerrar sidebar al hacer clic en un link (en móviles)
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('open');
            }
        });
    });

    // Click fuera del sidebar lo cierra (en móviles)
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 &&
            !sidebar.contains(event.target) &&
            event.target !== toggleSidebarBtn &&
            sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
        }
    });

    // Marcar sección actual en el menu
    function markCurrentSection() {
        const scrollPosition = window.scrollY;

        // Obtenemos todas las secciones y sus posiciones
        const sections = document.querySelectorAll('.section, .subsection');

        let currentSection = null;

        // Encontramos la sección actual basada en la posición del scroll
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (scrollPosition >= sectionTop) {
                currentSection = section.getAttribute('id');
            }
        });

        // Removemos la clase activa de todos los links
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
        });

        // Añadimos la clase activa al link correspondiente
        if (currentSection) {
            const activeLink = sidebar.querySelector(`a[href="#${currentSection}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
    }

    // Actualizamos la sección activa al hacer scroll
    window.addEventListener('scroll', markCurrentSection);

    // Llamar a markCurrentSection al cargar la página
    markCurrentSection();

    // Mejorar la visualización de los diagramas Mermaid
    function adjustMermaidDiagrams() {
        // Asegurarse de verificar tanto la nueva estructura como la anterior
        const diagrams = document.querySelectorAll('.mermaid');
        diagrams.forEach(diagram => {
            const svg = diagram.querySelector('svg');
            if (svg) {
                svg.style.maxWidth = '100%';
                svg.style.height = 'auto';
            }
        });
    }

    // Función para añadir funcionalidad de zoom y pan a los diagramas Mermaid
    function setupZoomAndPanForMermaidDiagrams() {
        document.querySelectorAll('.mermaid').forEach(diagram => {
            // Esperar a que Mermaid renderice el SVG
            setTimeout(() => {
                const svg = diagram.querySelector('svg');
                if (!svg) return;

                // Crear un contenedor para el SVG si no existe
                let container = diagram.parentElement;
                if (!container.classList.contains('diagram-container')) {
                    container = document.createElement('div');
                    container.className = 'diagram-container';
                    container.style.position = 'relative';
                    container.style.overflow = 'hidden';
                    container.style.width = '100%';
                    container.style.margin = '0 auto';

                    // Envolver el SVG en el contenedor
                    diagram.parentNode.insertBefore(container, diagram);
                    container.appendChild(diagram);
                }

                // Variables para el zoom y pan
                let scale = 1;
                let panning = false;
                let startX, startY;
                let translateX = 0;
                let translateY = 0;

                // Establecer transformación inicial
                svg.style.transformOrigin = 'center';
                svg.style.transition = 'transform 0.1s';
                svg.style.cursor = 'grab';

                // Función para establecer la transformación
                function setTransform() {
                    svg.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
                }

                // Evento de wheel para zoom - SOLO con CTRL presionado
                container.addEventListener('wheel', function(e) {
                    // Solo realizar zoom si Ctrl está presionado
                    if (e.ctrlKey) {
                        e.preventDefault(); // Prevenir scroll solo cuando hacemos zoom

                        const rect = svg.getBoundingClientRect();
                        const mouseX = e.clientX - rect.left;
                        const mouseY = e.clientY - rect.top;

                        // Determinar dirección del zoom (in/out)
                        const delta = e.deltaY < 0 ? 1.1 : 0.9;
                        const newScale = scale * delta;

                        // Limitar el zoom (min: 0.5, max: 5)
                        if (newScale > 0.5 && newScale < 5) {
                            // Punto de origen del zoom (posición del mouse)
                            const x = (mouseX - translateX) / scale;
                            const y = (mouseY - translateY) / scale;

                            // Actualizar escala y posición
                            scale = newScale;
                            translateX = mouseX - x * scale;
                            translateY = mouseY - y * scale;

                            setTransform();
                        }
                    }
                    // No hacemos return false para permitir el scroll normal cuando Ctrl no está presionado
                }, { passive: false });

                // Eventos para arrastrar (pan)
                svg.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    panning = true;
                    startX = e.clientX - translateX;
                    startY = e.clientY - translateY;
                    svg.style.cursor = 'grabbing';
                });

                window.addEventListener('mousemove', function(e) {
                    if (!panning) return;
                    e.preventDefault();
                    translateX = e.clientX - startX;
                    translateY = e.clientY - startY;
                    setTransform();
                });

                window.addEventListener('mouseup', function() {
                    panning = false;
                    svg.style.cursor = 'grab';
                });

                // Doble click para resetear zoom y posición
                svg.addEventListener('dblclick', function(e) {
                    e.preventDefault();
                    scale = 1;
                    translateX = 0;
                    translateY = 0;
                    setTransform();
                });

                // Info sobre uso
                const infoElement = document.createElement('div');
                infoElement.className = 'diagram-info';
                infoElement.innerHTML = 'Usa Ctrl + rueda del ratón para hacer zoom. Arrastra para mover. Doble click para resetear.';
                infoElement.style.position = 'absolute';
                infoElement.style.bottom = '5px';
                infoElement.style.right = '5px';
                infoElement.style.fontSize = '12px';
                infoElement.style.color = '#666';
                infoElement.style.background = 'rgba(255,255,255,0.7)';
                infoElement.style.padding = '2px 5px';
                infoElement.style.borderRadius = '3px';
                infoElement.style.zIndex = '100';
                container.appendChild(infoElement);

                // Mostrar info solo al hover
                infoElement.style.opacity = '0';
                container.addEventListener('mouseenter', () => {
                    infoElement.style.opacity = '1';
                });
                container.addEventListener('mouseleave', () => {
                    infoElement.style.opacity = '0';
                });
            }, 1500); // Esperar 1.5 segundos para asegurar que Mermaid haya terminado de renderizar
        });
    }

    // Iniciar la funcionalidad de zoom y pan después de cargar la página
    setTimeout(setupZoomAndPanForMermaidDiagrams, 1500);

    // Reintentar la inicialización de Mermaid después de un breve retraso
    setTimeout(function() {
        try {
            // Método alternativo para renderizar: contentLoaded() recarga los diagramas
            mermaid.contentLoaded();
            adjustMermaidDiagrams();
            console.log('Mermaid reloaded successfully');
            // Reiniciar zoom y pan después de recargar Mermaid
            setupZoomAndPanForMermaidDiagrams();
        } catch (e) {
            console.error('Error al reinicializar Mermaid:', e);
        }
    }, 1000);

    // Ajustar diagramas al cambiar el tamaño de la ventana
    window.addEventListener('resize', function() {
        // Re-aplicar zoom y pan cuando cambie el tamaño
        setupZoomAndPanForMermaidDiagrams();
    });
});
