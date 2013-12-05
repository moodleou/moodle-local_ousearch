<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang strings.
 * @package local
 * @subpackage ousearch
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['ousearch'] = 'OU search';
$string['searchfor']='Buscar: {$a}';
$string['untitled']='(Sin título)';
$string['searchresultsfor'] = 'Buscar resultados para <strong>{$a}</strong>';
$string['noresults'] = 'No se han encontrado resultados. Prueba utilizando palabras diferentes o
eliminando palabras en la consulta';
$string['nomoreresults'] = 'No hay más resultados.';
$string['previousresults']='Volver a los resultados {$a}';
$string['findmoreresults']='Más resultados';
$string['searchtime']='Tiempo de búsqueda: {$a}s';
$string['resultsfail']='No se han encontrado resultados que incluyan la palabra <strong>{$a}</strong>. Prueba
utilizando palabras diferentes';
$string['remote'] = 'Permitir búsqueda remota a las IP';
$string['configremote']= 'Lista de direcciones IP a las que se permite utilizar la utilidad de búsqueda remota.
Esto debería ser una lista vacía o con una o más direcciones IP, separadas por coma. ¡Ten cuidado!
Las peticiones desde estas direcciones IP pueden buscar (y ver el resumen del texto) como si fueran un
usuario. Por defecto está vacío, prohibiendo este acceso.';
$string['displayversion'] = 'Versión de OU search: <strong>{$a}</strong>';
$string['nowordsinquery'] = 'Introduce algunas palabras en el campo de búsqueda.';
$string['reindex'] = 'Reindexando los documentos del módulo {$a->module} en el curso {$a->courseid}';

$string['fastinserterror'] = 'Fallo en la inserción de los datos de búsqueda (inserción de alto rendimiento)';
$string['remotewrong'] = 'El acceso remoto de búsqueda no está configurado (o no está configurado correctamente).';
$string['remotenoaccess'] = 'Esta dirección IP no tiene acceso a la búsqueda remota';
$string['pluginname'] = $string['ousearch'];
$string['restofwebsite'] = 'Buscar en el resto del sitio web';
$string['toomanyterms'] = '<strong>Has introducido demasiados términos(palabras) en la consulta.</strong> Para asegurar que los resultados de la consulta puedan ser calculados con rapidez, el sistema limita las búsquedas a un máximo de {$a} palabras. Por favor, pulsa el botón "Atrás" en tu navegador y modifica la consulta.';
$string['maxterms'] = 'Máximo número de términos';
$string['maxterms_desc'] = 'Si el usuario intena buscar más términos que este límite, se mostrará un mensaje de error. (Por cuestión de rendimiento.)';
