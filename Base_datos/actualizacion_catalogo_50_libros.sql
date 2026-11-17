-- ============================================================
-- BiblioLend · Actualización del catálogo a 50 libros
-- Base de datos: biblioteca
-- ============================================================
-- Este script:
--   1. Guarda una copia de la tabla libro en libro_backup_catalogo.
--   2. Inserta o actualiza los libros con id 1 a 50.
--   3. Deja el AUTO_INCREMENT listo para el libro 51.
--
-- Ejecutar con:
--   /opt/lampp/bin/mysql -u root biblioteca < Base_datos/actualizacion_catalogo_50_libros.sql
--
-- ADVERTENCIA: los libros con id 1 a 50 serán reemplazados por
-- los del nuevo catálogo. La copia de seguridad permite recuperarlos.
-- ============================================================

SET NAMES utf8mb4;
START TRANSACTION;

DROP TABLE IF EXISTS libro_backup_catalogo;
CREATE TABLE libro_backup_catalogo AS SELECT * FROM libro;

INSERT INTO libro
  (id_libro, titulo, autor, categoria, descripcion, portada, copias, disponibles)
VALUES
(1, 'Leyendas de Nuestra América', 'Varios autores', 'Fábulas y leyendas', 'Recopilación de leyendas tradicionales de América Latina, con relatos que mezclan historia, naturaleza y fantasía.', 'Imagenes/Libros/01_leyendas_de_nuestra_america.jpg', 3, 3),
(2, 'Los mejores relatos históricos', 'Varios autores', 'Historia', 'Selección de relatos históricos que acercan a los lectores a épocas y personajes importantes de la humanidad.', 'Imagenes/Libros/02_los_mejores_relatos_historicos.jpg', 3, 3),
(3, 'Castor y Frip lo reparan todo', 'Lars Klinting', 'Literatura infantil', 'Castor y Frip encuentran cosas que reparar y demuestran que con ingenio y colaboración todo puede funcionar mejor.', 'Imagenes/Libros/03_castor_y_frip_lo_reparan_todo.jpg', 3, 3),
(4, 'Cuentos y leyendas de los Trumai', 'Varios autores', 'Fábulas y leyendas', 'Colección de cuentos y leyendas del pueblo Trumai, una ventana a la tradición oral indígena de la Amazonía.', 'Imagenes/Libros/04_cuentos_y_leyendas_de_los_trumai.jpg', 3, 3),
(5, 'El misterio del pollo en la batea', 'Autor por confirmar', 'Literatura infantil', 'Un relato de misterio y humor en el que un pollo aparece en el lugar menos esperado.', 'Imagenes/Libros/05_el_misterio_del_pollo_en_la_batea.png', 3, 3),
(6, 'Chigüiro', 'Ivar Da Coll', 'Literatura infantil', 'Las aventuras de Chigüiro, un pequeño roedor que descubre el mundo con curiosidad y ternura.', 'Imagenes/Libros/06_chiguiro.jpg', 3, 3),
(7, 'Marte y las princesas voladoras', 'Autor por confirmar', 'Literatura infantil', 'Una historia de imaginación y amistad que viaja entre Marte, princesas y voladoras.', 'Imagenes/Libros/07_marte_y_las_princesas_voladoras.jpg', 3, 3),
(8, 'El hombre y su cultura', 'Autor por confirmar', 'Consulta', 'Un recorrido por las distintas formas en que los seres humanos crean cultura, costumbres y sociedad.', 'Imagenes/Libros/08_el_hombre_y_su_cultura.jpg', 3, 3),
(9, 'Las lágrimas del cocodrilo', 'Autor por confirmar', 'Literatura infantil', 'Un cuento sobre emociones, empatía y las lágrimas que a veces esconden los cocodrilos.', 'Imagenes/Libros/09_las_lagrimas_del_cocodrilo.jpg', 3, 3),
(10, 'Mujeres inolvidables', 'Varios autores', 'Biografía', 'Retratos de mujeres que dejaron huella en la historia, la ciencia, el arte y la vida social.', 'Imagenes/Libros/10_mujeres_inolvidables.jpg', 3, 3),
(11, 'Planeta vivo', 'Autor por confirmar', 'Ciencias naturales', 'Un libro para descubrir la diversidad de la vida en la Tierra y la importancia de cuidar los ecosistemas.', 'Imagenes/Libros/11_planeta_vivo.jpg', 3, 3),
(12, 'Reciclamos', 'Autor por confirmar', 'Ciencias naturales', 'Propuestas y actividades para aprender a reciclar y reducir el impacto de los residuos en el ambiente.', 'Imagenes/Libros/12_reciclamos.jpg', 3, 3),
(13, 'Titiritesa', 'Autor por confirmar', 'Literatura infantil', 'Las aventuras de Titiritesa, un personaje lleno de ingenio que enfrenta desafíos con valentía.', 'Imagenes/Libros/13_titiritesa.jpg', 3, 3),
(14, 'Las ballenas y otros mamíferos marinos', 'Autor por confirmar', 'Ciencias naturales', 'Información ilustrada sobre las ballenas, los delfines y otros mamíferos marinos.', 'Imagenes/Libros/14_las_ballenas_y_otros_mamiferos_marinos.jpg', 3, 3),
(15, 'La peor señora del mundo', 'Francisco Hinojosa', 'Literatura infantil', 'La historia de una señora tan terrible que todos aprenden a convivir con sus ocurrencias.', 'Imagenes/Libros/15_la_peor_senora_del_mundo.jpg', 3, 3),
(16, 'Juegos de África', 'Varios autores', 'Consulta', 'Juegos, tradiciones y relatos de África para conocer su riqueza cultural.', 'Imagenes/Libros/16_juegos_de_africa.jpg', 3, 3),
(17, 'El ruiseñor', 'Hans Christian Andersen', 'Literatura infantil', 'El clásico cuento de Hans Christian Andersen sobre un ruiseñor, la música y la verdadera belleza.', 'Imagenes/Libros/17_el_ruisenor.jpg', 3, 3),
(18, 'Disfruta con la fábula', 'Varios autores', 'Fábulas y leyendas', 'Una selección de fábulas con moralejas para disfrutar de la lectura y reflexionar sobre los valores.', 'Imagenes/Libros/18_disfruta_con_la_fabula.jpg', 3, 3),
(19, 'Me voy a casar con Tania', 'Autor por confirmar', 'Literatura infantil', 'Una historia sobre planes, ilusiones y las sorpresas que trae crecer.', 'Imagenes/Libros/19_me_voy_a_casar_con_tania.jpg', 3, 3),
(20, 'La bella y la bestia', 'Jeanne-Marie Leprince de Beaumont', 'Literatura infantil', 'La clásica historia de amor entre una bella joven y una bestia, adaptada para lectores escolares.', 'Imagenes/Libros/20_la_bella_y_la_bestia.jpg', 3, 3),
(21, 'La bella de los cabellos de oro', 'Autor por confirmar', 'Literatura infantil', 'Un relato de hadas sobre una joven de cabellos de oro y las pruebas que debe superar.', 'Imagenes/Libros/21_la_bella_de_los_cabellos_de_oro.jpg', 3, 3),
(22, 'El cazador de leones', 'Autor por confirmar', 'Literatura infantil', 'La aventura de un cazador que aprende a respetar a los animales y a la naturaleza.', 'Imagenes/Libros/22_el_cazador_de_leones.jpg', 3, 3),
(23, 'Doce cuentos', 'Varios autores', 'Literatura infantil', 'Una colección de doce cuentos breves para leer, imaginar y compartir.', 'Imagenes/Libros/23_doce_cuentos.jpg', 3, 3),
(24, 'Robin Hood', 'Anónimo', 'Aventura', 'Las aventuras del arquero Robin Hood y su banda en el bosque de Sherwood.', 'Imagenes/Libros/24_robin_hood.jpg', 3, 3),
(25, 'Cuentos de Tomás Carrasquilla', 'Tomás Carrasquilla', 'Literatura infantil', 'Relatos del escritor colombiano Tomás Carrasquilla, con personajes y paisajes de Antioquia.', 'Imagenes/Libros/25_cuentos_de_tomas_carrasquilla.jpg', 3, 3),
(26, 'Crónicas y relatos de la independencia', 'Varios autores', 'Historia', 'Crónicas y relatos que reconstruyen los hechos y protagonistas de la independencia.', 'Imagenes/Libros/26_cronicas_y_relatos_de_la_independencia.jpg', 3, 3),
(27, 'La historia de la civilización', 'Autor por confirmar', 'Historia', 'Un recorrido por los grandes momentos y aportes de la civilización humana.', 'Imagenes/Libros/27_la_historia_de_la_civilizacion.jpg', 3, 3),
(28, 'Animales asombrosos', 'Autor por confirmar', 'Ciencias naturales', 'Datos e imágenes sorprendentes sobre animales de todo el mundo.', 'Imagenes/Libros/28_animales_asombrosos.jpg', 3, 3),
(29, 'Adivinanzas azules', 'Varios autores', 'Literatura infantil', 'Adivinanzas en verso para jugar con las palabras y poner a prueba la imaginación.', 'Imagenes/Libros/29_adivinanzas_azules.jpg', 3, 3),
(30, 'Aprendo a contar', 'Autor por confirmar', 'Consulta', 'Un libro para aprender los números y contar de forma divertida.', 'Imagenes/Libros/30_aprendo_a_contar.jpg', 3, 3),
(31, 'Cuentos de Navidad', 'Varios autores', 'Literatura infantil', 'Cuentos navideños para compartir en familia y celebrar la época más cálida del año.', 'Imagenes/Libros/31_cuentos_de_navidad.jpg', 3, 3),
(32, 'El caballo de Troya', 'Anónimo', 'Aventura', 'La historia del caballo de Troya y el ingenio que cambió el rumbo de una guerra.', 'Imagenes/Libros/32_el_caballo_de_troya.jpg', 3, 3),
(33, 'El caimán soñador', 'Autor por confirmar', 'Literatura infantil', 'El caimán soñador invita a viajar por el mundo de los sueños y la imaginación.', 'Imagenes/Libros/33_el_caiman_sonador.jpg', 3, 3),
(34, 'El gran libro de los valores', 'Varios autores', 'Consulta', 'Relatos y reflexiones para aprender valores como el respeto, la honestidad y la solidaridad.', 'Imagenes/Libros/34_el_gran_libro_de_los_valores.jpg', 3, 3),
(35, 'El último mohicano', 'James Fenimore Cooper', 'Aventura', 'Las aventuras de un explorador en la frontera, en una de las novelas clásicas de aventura.', 'Imagenes/Libros/35_el_ultimo_mohicano.jpg', 3, 3),
(36, 'Electricidad y magnetismo', 'Autor por confirmar', 'Ciencias naturales', 'Explicaciones y experimentos sencillos sobre la electricidad y el magnetismo.', 'Imagenes/Libros/36_electricidad_y_magnetismo.jpg', 3, 3),
(37, 'Electrónica e informática', 'Autor por confirmar', 'Consulta', 'Introducción al mundo de la electrónica, los circuitos y la informática.', 'Imagenes/Libros/37_electronica_e_informatica.jpg', 3, 3),
(38, 'Geografía para niños', 'Autor por confirmar', 'Geografía', 'Un atlas para conocer países, relieves, climas y culturas del mundo.', 'Imagenes/Libros/38_geografia_para_ninos.jpg', 3, 3),
(39, 'Cómo entrenar a tu dragón', 'Cressida Cowell', 'Aventura', 'Las aventuras de un joven vikingo que aprende a entrenar a su dragón.', 'Imagenes/Libros/39_como_entrenar_a_tu_dragon.jpg', 3, 3),
(40, 'Si lo crees, lo creas', 'Autor por confirmar', 'Consulta', 'Una invitación a creer en uno mismo y a transformar los pensamientos en acciones.', 'Imagenes/Libros/40_si_lo_crees_lo_creas.jpg', 3, 3),
(41, 'Cien años de soledad', 'Gabriel García Márquez', 'Novela', 'La novela de Gabriel García Márquez sobre la familia Buendía y el pueblo de Macondo.', 'Imagenes/Libros/41_cien_anos_de_soledad.jpg', 3, 3),
(42, 'La energía', 'Autor por confirmar', 'Ciencias naturales', 'Un libro para comprender qué es la energía, cómo se transforma y por qué es importante.', 'Imagenes/Libros/42_la_energia.jpg', 3, 3),
(43, 'La independencia de Colombia, así fue', 'Varios autores', 'Historia', 'Relato ilustrado sobre los hechos y personajes de la independencia de Colombia.', 'Imagenes/Libros/43_la_independencia_de_colombia_asi_fue.jpg', 3, 3),
(44, 'Los trabajos de Hércules', 'Anónimo', 'Fábulas y leyendas', 'Las doce pruebas de Hércules, uno de los relatos más conocidos de la mitología griega.', 'Imagenes/Libros/44_los_trabajos_de_hercules.jpg', 3, 3),
(45, 'Magia de los mitos y leyendas latinoamericanas', 'Varios autores', 'Fábulas y leyendas', 'Una recopilación de mitos y leyendas latinoamericanas llenos de magia y tradición.', 'Imagenes/Libros/45_magia_de_los_mitos_y_leyendas_latinoamericanas.jpg', 3, 3),
(46, 'Máquinas y herramientas', 'Autor por confirmar', 'Consulta', 'Información sobre máquinas, herramientas y los principios que hacen funcionar la tecnología.', 'Imagenes/Libros/46_maquinas_y_herramientas.jpg', 3, 3),
(47, 'Mitos y leyendas colombianos', 'Varios autores', 'Fábulas y leyendas', 'Mitos y leyendas de Colombia para descubrir la tradición oral del país.', 'Imagenes/Libros/47_mitos_y_leyendas_colombianos.jpg', 3, 3),
(48, 'Origami para niños', 'Autor por confirmar', 'Consulta', 'Instrucciones y proyectos de origami para crear figuras con papel.', 'Imagenes/Libros/48_origami_para_ninos.jpg', 3, 3),
(49, 'Sistema solar', 'Autor por confirmar', 'Ciencias naturales', 'Un viaje por el sistema solar para conocer el Sol, los planetas y sus lunas.', 'Imagenes/Libros/49_sistema_solar.jpg', 3, 3),
(50, 'El gran libro de los experimentos', 'Varios autores', 'Consulta', 'Experimentos sencillos para aprender ciencia desde la práctica y la curiosidad.', 'Imagenes/Libros/50_el_gran_libro_de_los_experimentos.jpg', 3, 3)
ON DUPLICATE KEY UPDATE
  titulo = VALUES(titulo),
  autor = VALUES(autor),
  categoria = VALUES(categoria),
  descripcion = VALUES(descripcion),
  portada = VALUES(portada),
  copias = VALUES(copias),
  disponibles = VALUES(disponibles);

ALTER TABLE libro AUTO_INCREMENT = 51;
COMMIT;
