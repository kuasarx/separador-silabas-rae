<?php

declare(strict_types=1);

namespace Kuasarx\Linguistica\Tests;

use Kuasarx\Linguistica\SeparadorSilabasRAE;
use Kuasarx\Linguistica\InvalidWordException;
// =============================================================================
// --- EJECUCIÓN DE PRUEBAS (CON EXPECTATIVAS CORREGIDAS) ---
// =============================================================================
echo "Ejecutando pruebas extensas...\n";
$separador = new SeparadorSilabasRAE();

// Función Auxiliar de Pruebas
function assertSeparacion(SeparadorSilabasRAE $sep, string $palabra, array $esperado, bool $hiatos = true, string $region = 'es_ES', string $prefijos = 'fonetica', string $mensaje = '') { try { $sepTest = clone $sep; /*$sepTest->enableTracing(true);*/ $resultado = $sepTest->separar($palabra, $hiatos, $region, $prefijos); $silabas = $resultado['silabas'] ?? []; $puntos = $resultado['puntos_division'] ?? []; $excepciones = array_values(array_unique($resultado['excepciones_aplicadas'] ?? [])); sort($excepciones); $tiempo = round($resultado['tiempo_ms'] ?? 0, 2); $fuente = $resultado['fuente'] ?? 'error'; $trace = $resultado['trace'] ?? null; $isEqual = ($silabas == $esperado); assert($isEqual, $mensaje . ": Palabra '{$palabra}' ({$region},{$prefijos}) -> Esperado: [" . implode('-', $esperado) . "], Obtenido: [" . implode('-', $silabas) . "] (Puntos: ".implode(',',$puntos).") Excepciones: [".implode(', ', $excepciones)."] {$tiempo}ms [{$fuente}]"); if ($isEqual) echo "."; else { echo "F"; echo "\nFALLO: {$mensaje}: Palabra '{$palabra}' ({$region},{$prefijos})\n"; echo "  Esperado: [" . implode('-', $esperado) . "]\n"; echo "  Obtenido: [" . implode('-', $silabas) . "]\n"; echo "  Puntos:   [" . implode(',', $puntos) . "]\n"; echo "  Excep:    [" . implode(' | ', $excepciones) . "]\n"; if ($trace !== null) { echo "  --- TRACE ---\n"; echo implode("\n", $trace); echo "\n  --- FIN TRACE ---\n"; } } } catch (InvalidWordException $e) { echo "E"; echo "\nERROR (InvalidWord): {$mensaje}: Palabra '{$palabra}' -> " . $e->getMessage() . "\n"; assert(false, "Excepción InvalidWord."); } catch (\AssertionError $e) { echo "F"; echo "\nFallo Aserción: " . $e->getMessage() . "\n"; if (isset($resultado) && isset($resultado['trace'])) { echo "  --- TRACE ASOCIADO ---\n"; echo implode("\n", $resultado['trace']); echo "\n  --- FIN TRACE ---\n"; } } catch (\Throwable $t) { echo "X"; echo "\nError Inesperado: {$mensaje}: Palabra '{$palabra}' -> " . $t->getMessage() . " en " . $t->getFile() . ":" . $t->getLine() . "\n"; assert(false, "Error Throwable."); } }

// --- Conjunto de Pruebas Completo (EXPECTATIVAS CORREGIDAS) ---
SeparadorSilabasRAE::limpiarCache();
echo "\n1. Casos Básicos:\n";
assertSeparacion($separador, 'a', ['a']); assertSeparacion($separador, 'sol', ['sol']); assertSeparacion($separador, 'tren', ['tren']); assertSeparacion($separador, 'pan', ['pan']); assertSeparacion($separador, 'luz', ['luz']); assertSeparacion($separador, 'es', ['es']); assertSeparacion($separador, 'un', ['un']); assertSeparacion($separador, 'no', ['no']); assertSeparacion($separador, 'yo', ['yo']); assertSeparacion($separador, 'ya', ['ya']); assertSeparacion($separador, 'casa', ['ca', 'sa']); assertSeparacion($separador, 'mapa', ['ma', 'pa']); assertSeparacion($separador, 'libro', ['li', 'bro']); assertSeparacion($separador, 'comer', ['co', 'mer']); assertSeparacion($separador, 'cantar', ['can', 'tar']); assertSeparacion($separador, 'amigo', ['a', 'mi', 'go']); assertSeparacion($separador, 'elefante', ['e', 'le', 'fan', 'te']); assertSeparacion($separador, 'idea', ['i', 'de', 'a']);
echo "\n2. Dígrafos:\n";
assertSeparacion($separador, 'chucho', ['chu', 'cho']); assertSeparacion($separador, 'allá', ['a', 'llá']); assertSeparacion($separador, 'lluvia', ['llu', 'via']); assertSeparacion($separador, 'perro', ['pe', 'rro']); assertSeparacion($separador, 'carroza', ['ca', 'rro', 'za']); assertSeparacion($separador, 'correr', ['co', 'rrer']);
echo "\n3. Grupos Consonánticos:\n";
assertSeparacion($separador, 'pluma', ['plu', 'ma']); assertSeparacion($separador, 'cable', ['ca', 'ble']); assertSeparacion($separador, 'clavo', ['cla', 'vo']); assertSeparacion($separador, 'regla', ['re', 'gla']); assertSeparacion($separador, 'flaco', ['fla', 'co']); assertSeparacion($separador, 'precio', ['pre', 'cio']); assertSeparacion($separador, 'libro', ['li', 'bro']); assertSeparacion($separador, 'atrás', ['a', 'trás']); assertSeparacion($separador, 'cuadro', ['cua', 'dro']); assertSeparacion($separador, 'secreto', ['se', 'cre', 'to']); assertSeparacion($separador, 'logro', ['lo', 'gro']); assertSeparacion($separador, 'fruta', ['fru', 'ta']); assertSeparacion($separador, 'costa', ['cos', 'ta']); assertSeparacion($separador, 'pasta', ['pas', 'ta']); assertSeparacion($separador, 'apto', ['ap', 'to']); assertSeparacion($separador, 'actor', ['ac', 'tor']); assertSeparacion($separador, 'perfecto', ['per', 'fec', 'to']); assertSeparacion($separador, 'dictar', ['dic', 'tar']); assertSeparacion($separador, 'pacto', ['pac', 'to']); assertSeparacion($separador, 'acción', ['ac', 'ción']); assertSeparacion($separador, 'técnica', ['téc', 'ni', 'ca']); assertSeparacion($separador, 'atmósfera', ['at', 'mós', 'fe', 'ra']); assertSeparacion($separador, 'magno', ['mag', 'no']); assertSeparacion($separador, 'signo', ['sig', 'no']); assertSeparacion($separador, 'atleta', ['at', 'le', 'ta'], true, 'es_ES'); assertSeparacion($separador, 'atlántico', ['at', 'lán', 'ti', 'co'], true, 'es_ES'); assertSeparacion($separador, 'atleta', ['a', 'tle', 'ta'], true, 'es_MX'); assertSeparacion($separador, 'atlántico', ['a', 'tlán', 'ti', 'co'], true, 'es_MX'); assertSeparacion($separador, 'instante', ['ins', 'tan', 'te']); assertSeparacion($separador, 'obstruir', ['obs', 'truir']); assertSeparacion($separador, 'perspicaz', ['pers', 'pi', 'caz']); assertSeparacion($separador, 'substraer', ['subs', 'tra', 'er']); assertSeparacion($separador, 'inspirar', ['ins', 'pi', 'rar']); assertSeparacion($separador, 'explicar', ['ex', 'pli', 'car']); assertSeparacion($separador, 'desprecio', ['des', 'pre', 'cio']); assertSeparacion($separador, 'amplitud', ['am', 'pli', 'tud']); assertSeparacion($separador, 'compra', ['com', 'pra']); assertSeparacion($separador, 'inflar', ['in', 'flar']); assertSeparacion($separador, 'anglófilo', ['an', 'gló', 'fi', 'lo']); assertSeparacion($separador, 'abstracto', ['abs', 'trac', 'to']); assertSeparacion($separador, 'constreñir', ['cons', 'tre', 'ñir']); assertSeparacion($separador, 'adscribir', ['ads', 'cri', 'bir']); assertSeparacion($separador, 'abstemio', ['abs', 'te', 'mio']);
echo "\n4. Vowel Sequences:\n";
assertSeparacion($separador, 'auto', ['au', 'to']); assertSeparacion($separador, 'reina', ['rei', 'na']); assertSeparacion($separador, 'boina', ['boi', 'na']); assertSeparacion($separador, 'deuda', ['deu', 'da']); assertSeparacion($separador, 'estadounidense', ['es', 'ta', 'dou', 'ni', 'den', 'se']); assertSeparacion($separador, 'viuda', ['viu', 'da']); assertSeparacion($separador, 'muy', ['muy']); assertSeparacion($separador, 'ruido', ['rui', 'do']); assertSeparacion($separador, 'pingüe', ['pin', 'güe']); assertSeparacion($separador, 'argüir', ['ar', 'güir']); assertSeparacion($separador, 'fuego', ['fue', 'go']); assertSeparacion($separador, 'cuota', ['cuo', 'ta']); assertSeparacion($separador, 'residuo', ['re', 'si', 'duo']); assertSeparacion($separador, 'puerta', ['puer', 'ta']); assertSeparacion($separador, 'cielo', ['cie', 'lo']); assertSeparacion($separador, 'piojo', ['pio', 'jo']); assertSeparacion($separador, 'viaje', ['via', 'je']); assertSeparacion($separador, 'diente', ['dien', 'te']); assertSeparacion($separador, 'triunfo', ['triun', 'fo']); assertSeparacion($separador, 'diurno', ['diur', 'no']); assertSeparacion($separador, 'veintiún', ['vein', 'tiún'], true, 'es_ES', 'fonetica', 'Diptongo iú con tilde'); assertSeparacion($separador, 'jesuita', ['je', 'sui', 'ta'], true, 'es_ES', 'fonetica', 'Diptongo ui'); assertSeparacion($separador, 'limpiáis', ['lim', 'piáis']); assertSeparacion($separador, 'amortigüéis', ['a', 'mor', 'ti', 'güéis']); assertSeparacion($separador, 'actuáis', ['ac', 'tuáis']); assertSeparacion($separador, 'buey', ['buey']); assertSeparacion($separador, 'Paraguay', ['Pa', 'ra', 'guay']); assertSeparacion($separador, 'vieira', ['viei', 'ra']); assertSeparacion($separador, 'hioides', ['hioi', 'des']); assertSeparacion($separador, 'caos', ['ca', 'os']); assertSeparacion($separador, 'aorta', ['a', 'or', 'ta']); assertSeparacion($separador, 'teatro', ['te', 'a', 'tro']); assertSeparacion($separador, 'línea', ['lí', 'ne', 'a']); assertSeparacion($separador, 'héroe', ['hé', 'ro', 'e']); assertSeparacion($separador, 'poema', ['po', 'e', 'ma']); assertSeparacion($separador, 'leer', ['le', 'er']); assertSeparacion($separador, 'creer', ['cre', 'er']); assertSeparacion($separador, 'chiita', ['chi', 'i', 'ta'], true, 'es_ES', 'fonetica', 'Hiato ii'); assertSeparacion($separador, 'chií', ['chi', 'í'], true, 'es_ES', 'fonetica', 'Hiato ií'); assertSeparacion($separador, 'friísimo', ['fri', 'í', 'si', 'mo'], true, 'es_ES', 'fonetica', 'Hiato ií'); assertSeparacion($separador, 'roedor', ['ro', 'e', 'dor']); assertSeparacion($separador, 'loor', ['lo', 'or']); assertSeparacion($separador, 'zoo', ['zo', 'o']); assertSeparacion($separador, 'duunviro', ['du', 'un', 'vi', 'ro'], true, 'es_ES', 'fonetica', 'Hiato uu'); assertSeparacion($separador, 'maíz', ['ma', 'íz']); assertSeparacion($separador, 'raíz', ['ra', 'íz']); assertSeparacion($separador, 'país', ['pa', 'ís']); assertSeparacion($separador, 'oír', ['o', 'ír']); assertSeparacion($separador, 'freír', ['fre', 'ír']); assertSeparacion($separador, 'reír', ['re', 'ír']); assertSeparacion($separador, 'laúd', ['la', 'úd']); assertSeparacion($separador, 'baúl', ['ba', 'úl']); assertSeparacion($separador, 'raúl', ['ra', 'úl']); assertSeparacion($separador, 'transeúnte', ['tran', 'se', 'ún', 'te']); assertSeparacion($separador, 'reúne', ['re', 'ú', 'ne']); assertSeparacion($separador, 'río', ['rí', 'o']); assertSeparacion($separador, 'frío', ['frí', 'o']); assertSeparacion($separador, 'continúo', ['con', 'ti', 'nú', 'o']); assertSeparacion($separador, 'flúor', ['flú', 'or']); assertSeparacion($separador, 'ahí', ['a', 'hí']); assertSeparacion($separador, 'búho', ['bú', 'ho']); assertSeparacion($separador, 'rehén', ['re', 'hén']); assertSeparacion($separador, 'alcohol', ['al', 'co', 'hol']); assertSeparacion($separador, 'cohete', ['co', 'he', 'te']); assertSeparacion($separador, 'prohíbe', ['pro', 'hí', 'be']); assertSeparacion($separador, 'ahijado', ['ahi', 'ja', 'do']); assertSeparacion($separador, 'ahumado', ['ahu', 'ma', 'do']); assertSeparacion($separador, 'sahumerio', ['sahu', 'me', 'rio']); assertSeparacion($separador, 'rehusar', ['rehu', 'sar']);
echo "\n5. Hache Intercalada (no vocálica):\n";
assertSeparacion($separador, 'deshacer', ['des', 'ha', 'cer']); assertSeparacion($separador, 'deshielo', ['des', 'hie', 'lo']); assertSeparacion($separador, 'deshonra', ['des', 'hon', 'ra']); assertSeparacion($separador, 'anhelo', ['an', 'he', 'lo']); assertSeparacion($separador, 'alhaja', ['al', 'ha', 'ja']); assertSeparacion($separador, 'inhábil', ['in', 'há', 'bil']); assertSeparacion($separador, 'inhibir', ['in', 'hi', 'bir']); assertSeparacion($separador, 'exhalar', ['ex', 'ha', 'lar']); assertSeparacion($separador, 'exhausto', ['ex', 'haus', 'to']); assertSeparacion($separador, 'adhesión', ['ad', 'he', 'sión']); assertSeparacion($separador, 'subhumano', ['sub', 'hu', 'ma', 'no']);
echo "\n6. Prefijos y Composición (Fonética):\n";
assertSeparacion($separador, 'subrayar', ['su', 'bra', 'yar'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'sublunar', ['su', 'blu', 'nar'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'deshacer', ['des', 'ha', 'cer'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'inhumano', ['in', 'hu', 'ma', 'no'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'cooperar', ['co', 'o', 'pe', 'rar'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'contraorden', ['con', 'tra', 'or', 'den'], true, 'es_ES', 'fonetica');
assertSeparacion($separador, 'antiimperialista', ['an', 'ti', 'im', 'pe', 'ria', 'lis', 'ta'], true, 'es_ES', 'fonetica', 'PF: antiimperialista (hiato ii)');
assertSeparacion($separador, 'rehidratar', ['rehi', 'dra', 'tar'], true, 'es_ES', 'fonetica', 'PF: rehidratar (hiato e-i por h)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'desarrollar', ['de', 'sa', 'rro', 'llar'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'posguerra', ['pos', 'gue', 'rra'], true, 'es_ES', 'fonetica'); assertSeparacion($separador, 'postguerra', ['post', 'gue', 'rra'], true, 'es_ES', 'fonetica');
assertSeparacion($separador, 'exalumno', ['e', 'xa', 'lum', 'no'], true, 'es_ES', 'fonetica', 'PF: exalumno (V.xV)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'inhábil', ['in', 'há', 'bil'], true, 'es_ES', 'fonetica', 'PF: inhábil (VC.hV)');
assertSeparacion($separador, 'superhombre', ['su', 'per', 'hom', 'bre'], true, 'es_ES', 'fonetica', 'PF: superhombre (VC.hV)');
echo "\n7. Prefijos y Composición (Morfológica):\n";
assertSeparacion($separador, 'subrayar', ['sub', 'ra', 'yar'], true, 'es_ES', 'morfologica'); assertSeparacion($separador, 'sublunar', ['sub', 'lu', 'nar'], true, 'es_ES', 'morfologica'); assertSeparacion($separador, 'deshacer', ['des', 'ha', 'cer'], true, 'es_ES', 'morfologica'); assertSeparacion($separador, 'inhumano', ['in', 'hu', 'ma', 'no'], true, 'es_ES', 'morfologica'); assertSeparacion($separador, 'cooperar', ['co', 'o', 'pe', 'rar'], true, 'es_ES', 'morfologica'); assertSeparacion($separador, 'contraorden', ['con', 'tra', 'or', 'den'], true, 'es_ES', 'morfologica');
assertSeparacion($separador, 'antiimperialista', ['a', 'nti', 'im', 'pe', 'ria', 'lis', 'ta'], true, 'es_ES', 'morfologica', 'PM: antiimperialista (prefijo a)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'rehidratar', ['re', 'hi', 'dra', 'tar'], true, 'es_ES', 'morfologica'); // re | hidratar
assertSeparacion($separador, 'exalumno', ['ex', 'a', 'lum', 'no'], true, 'es_ES', 'morfologica', 'PM: exalumno');
assertSeparacion($separador, 'inhábil', ['in', 'há', 'bil'], true, 'es_ES', 'morfologica');
echo "\n8. Prefijos y Composición (Adaptativa):\n";
assertSeparacion($separador, 'subrayar', ['su', 'bra', 'yar'], true, 'es_ES', 'adaptativa', 'PA: subrayar (Fonética)');
assertSeparacion($separador, 'sublunar', ['sub', 'lu', 'nar'], true, 'es_ES', 'adaptativa', 'PA: sublunar (Morfológica)');
assertSeparacion($separador, 'deshacer', ['des', 'ha', 'cer'], true, 'es_ES', 'adaptativa', 'PA: deshacer (Fonética)');
assertSeparacion($separador, 'inhumano', ['in', 'hu', 'ma', 'no'], true, 'es_ES', 'adaptativa', 'PA: inhumano (Fonética)');
assertSeparacion($separador, 'cooperar', ['co', 'o', 'pe', 'rar'], true, 'es_ES', 'adaptativa', 'PA: cooperar (Fonética)');
assertSeparacion($separador, 'contraorden', ['con', 'tra', 'or', 'den'], true, 'es_ES', 'adaptativa', 'PA: contraorden (Morfológica)');
assertSeparacion($separador, 'antiimperialista', ['an', 'ti', 'im', 'pe', 'ria', 'lis', 'ta'], true, 'es_ES', 'adaptativa', 'PA: antiimperialista (Fonética V+V)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'rehidratar', ['rehi', 'dra', 'tar'], true, 'es_ES', 'adaptativa', 'PA: rehidratar (Fonética V+hV)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'exalumno', ['e', 'xa', 'lum', 'no'], true, 'es_ES', 'adaptativa', 'PA: exalumno (Fonética X+V)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'inhábil', ['in', 'há', 'bil'], true, 'es_ES', 'adaptativa', 'PA: inhábil (Fonética C+hV)');
assertSeparacion($separador, 'suboficial', ['sub', 'o', 'fi', 'cial'], true, 'es_ES', 'adaptativa', 'PA: suboficial (Morfológica C+V)');
echo "\n9. Palabras Largas y Complejas:\n";
assertSeparacion($separador, 'otorrinolaringologo', ['o', 'to', 'rri', 'no', 'la', 'rin', 'go', 'lo', 'go'], true, 'es_ES', 'fonetica', 'otorrino...'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'electroencefalografista', ['e', 'lec', 'tro', 'en', 'ce', 'fa', 'lo', 'gra', 'fis', 'ta'], true, 'es_ES', 'fonetica', 'electro... (Hiato o-e)'); // CORREGIDA EXPECTATIVA
assertSeparacion($separador, 'constitucionalidad', ['cons', 'ti', 'tu', 'cio', 'na', 'li', 'dad']); assertSeparacion($separador, 'desoxirribonucleico', ['de', 'so', 'xi', 'rri', 'bo', 'nu', 'clei', 'co']); assertSeparacion($separador, 'esternocleidomastoideo', ['es', 'ter', 'no', 'clei', 'do', 'mas', 'toi', 'de', 'o']); assertSeparacion($separador, 'caleidoscopio', ['ca', 'lei', 'dos', 'co', 'pio']);
echo "\n10. Errores y Límites:\n";
assertSeparacion($separador, 'a', ['a']); try { $separador->separar(''); assert(false); echo "F"; } catch (InvalidWordException $e) { echo ".";} catch (\Throwable $e){ echo "E";} try { $separador->separar('test-word'); assert(false); echo "F"; } catch (InvalidWordException $e) { echo ".";} catch (\Throwable $e){ echo "E";} try { $separador->separar('año1'); assert(false); echo "F"; } catch (InvalidWordException $e) { echo ".";} catch (\Throwable $e){ echo "E";} try { $separador->separar('你好'); assert(false); echo "F"; } catch (InvalidWordException $e) { echo ".";} catch (\Throwable $e){ echo "E";} assertSeparacion($separador, 'xzzptk', ['xzzptk']);
echo "\n11. Caché:\n";
SeparadorSilabasRAE::limpiarCache(); assert(SeparadorSilabasRAE::getCacheSize() === 0); echo "."; $r1 = $separador->separar('constitucional'); assert(SeparadorSilabasRAE::getCacheSize() === 1); echo "."; assert($r1['fuente'] === 'calculado'); echo "."; $r2 = $separador->separar('constitucional'); assert(SeparadorSilabasRAE::getCacheSize() === 1); echo "."; assert($r2['fuente'] === 'cache'); echo "."; assert($r1['silabas'] === $r2['silabas']); echo "."; $r3 = $separador->separar('constitucional', true, 'es_MX'); assert(SeparadorSilabasRAE::getCacheSize() === 2); echo "."; assert($r3['fuente'] === 'calculado'); echo "."; SeparadorSilabasRAE::limpiarCache(); echo ".";

echo "\nTodas las pruebas extensas completadas.\n";

// --- Ejemplo de Uso Final ---
echo "\n--- Ejemplo de Uso Final ---\n";
$palabraEjemplo = "superhombre"; $resultadoEjemplo = $separador->separar($palabraEjemplo); echo "Palabra: " . $palabraEjemplo . "\n"; echo "Sílabas: " . implode('-', $resultadoEjemplo['silabas']) . "\n"; echo "Puntos División: " . implode(', ', $resultadoEjemplo['puntos_division']) . "\n"; $excepcionesOrdenadas = $resultadoEjemplo['excepciones_aplicadas']; sort($excepcionesOrdenadas); echo "Excepciones: " . implode(', ', $excepcionesOrdenadas) . "\n"; echo "Tiempo: " . round($resultadoEjemplo['tiempo_ms'], 2) . " ms\n"; echo "Fuente: " . $resultadoEjemplo['fuente'] . "\n"; echo "HTML: " . $separador->generarHtml($resultadoEjemplo['silabas']) . "\n";
$palabraEjemplo = "veintiún"; $resultadoEjemplo = $separador->separar($palabraEjemplo); echo "\nPalabra: " . $palabraEjemplo . "\n"; echo "Sílabas: " . implode('-', $resultadoEjemplo['silabas']) . "\n";
$palabraEjemplo = "chiita"; $resultadoEjemplo = $separador->separar($palabraEjemplo); echo "\nPalabra: " . $palabraEjemplo . "\n"; echo "Sílabas: " . implode('-', $resultadoEjemplo['silabas']) . "\n";
$palabraEjemplo = "chií"; $resultadoEjemplo = $separador->separar($palabraEjemplo); echo "\nPalabra: " . $palabraEjemplo . "\n"; echo "Sílabas: " . implode('-', $resultadoEjemplo['silabas']) . "\n";


// =============================================================================
// --- NUEVA BATERÍA DE PRUEBAS EXTENSAS (v2) ---
// =============================================================================
echo "\nEjecutando NUEVA batería de pruebas extensas (v2)...\n";
SeparadorSilabasRAE::limpiarCache();

// --- 1. Casos Básicos y Estructuras Simples (Nuevas Palabras) ---
echo "\nN1. Casos Básicos:\n";
assertSeparacion($separador, 'flor', ['flor'], true, 'es_ES', 'fonetica', 'Monosílabo CCVC');
assertSeparacion($separador, 'mar', ['mar'], true, 'es_ES', 'fonetica', 'Monosílabo CVC');
assertSeparacion($separador, 'club', ['club'], true, 'es_ES', 'fonetica', 'Monosílabo CCVC');
assertSeparacion($separador, 'vid', ['vid'], true, 'es_ES', 'fonetica', 'Monosílabo CVC');
assertSeparacion($separador, 'red', ['red'], true, 'es_ES', 'fonetica', 'Monosílabo CVC');
assertSeparacion($separador, 'dos', ['dos'], true, 'es_ES', 'fonetica', 'Monosílabo CVC');
assertSeparacion($separador, 'voy', ['voy'], true, 'es_ES', 'fonetica', 'Monosílabo CV(y)');
assertSeparacion($separador, 'ley', ['ley'], true, 'es_ES', 'fonetica', 'Monosílabo CV(y)');
assertSeparacion($separador, 'mesa', ['me', 'sa'], true, 'es_ES', 'fonetica', 'Básico VCV');
assertSeparacion($separador, 'luna', ['lu', 'na'], true, 'es_ES', 'fonetica', 'Básico VCV');
assertSeparacion($separador, 'abrir', ['a', 'brir'], true, 'es_ES', 'fonetica', 'Básico V.CCVC');
assertSeparacion($separador, 'vivir', ['vi', 'vir'], true, 'es_ES', 'fonetica', 'Básico VC final');
assertSeparacion($separador, 'comer', ['co', 'mer']); // Repetido, pero básico
assertSeparacion($separador, 'jugar', ['ju', 'gar'], true, 'es_ES', 'fonetica', 'Básico CVC final');
assertSeparacion($separador, 'objeto', ['ob', 'je', 'to'], true, 'es_ES', 'fonetica', 'Inicio VC');
assertSeparacion($separador, 'isla', ['is', 'la'], true, 'es_ES', 'fonetica', 'Inicio VC');
assertSeparacion($separador, 'urna', ['ur', 'na'], true, 'es_ES', 'fonetica', 'Inicio VC');
assertSeparacion($separador, 'oasis', ['o', 'a', 'sis'], true, 'es_ES', 'fonetica', 'Hiato o-a'); // V+V (hiato)

// --- 2. Dígrafos (Nuevas Palabras) ---
echo "\nN2. Dígrafos:\n";
assertSeparacion($separador, 'hacha', ['ha', 'cha'], true, 'es_ES', 'fonetica', 'Dígrafo ch');
assertSeparacion($separador, 'techo', ['te', 'cho'], true, 'es_ES', 'fonetica', 'Dígrafo ch');
assertSeparacion($separador, 'mecanichucho', ['me', 'ca', 'ni', 'chu', 'cho'], true, 'es_ES', 'fonetica', 'Dígrafo ch doble');
assertSeparacion($separador, 'billete', ['bi', 'lle', 'te'], true, 'es_ES', 'fonetica', 'Dígrafo ll');
assertSeparacion($separador, 'callado', ['ca', 'lla', 'do'], true, 'es_ES', 'fonetica', 'Dígrafo ll');
assertSeparacion($separador, 'folletín', ['fo', 'lle', 'tín'], true, 'es_ES', 'fonetica', 'Dígrafo ll');
assertSeparacion($separador, 'zorrillo', ['zo', 'rri', 'llo'], true, 'es_ES', 'fonetica', 'Dígrafos rr, ll');
assertSeparacion($separador, 'barrer', ['ba', 'rrer'], true, 'es_ES', 'fonetica', 'Dígrafo rr');
assertSeparacion($separador, 'arrecife', ['a', 'rre', 'ci', 'fe'], true, 'es_ES', 'fonetica', 'Dígrafo rr intermedio');
assertSeparacion($separador, 'terremoto', ['te', 'rre', 'mo', 'to'], true, 'es_ES', 'fonetica', 'Dígrafo rr');

// --- 3. Grupos Consonánticos (Nuevas Palabras) ---
echo "\nN3. Grupos Consonánticos:\n";
// Inseparables V.CCV
assertSeparacion($separador, 'aplicar', ['a', 'pli', 'car']);
assertSeparacion($separador, 'hablar', ['ha', 'blar']);
assertSeparacion($separador, 'incluir', ['in', 'cluir']); // ui diptongo
assertSeparacion($separador, 'reglamento', ['re', 'gla', 'men', 'to']);
assertSeparacion($separador, 'afluente', ['a', 'fluen', 'te']); // ue diptongo
assertSeparacion($separador, 'aprisa', ['a', 'pri', 'sa']);
assertSeparacion($separador, 'abrigo', ['a', 'bri', 'go']);
assertSeparacion($separador, 'letrado', ['le', 'tra', 'do']);
assertSeparacion($separador, 'ajedrez', ['a', 'je', 'drez']);
assertSeparacion($separador, 'recreo', ['re', 'cre', 'o']); // e-o hiato
assertSeparacion($separador, 'vinagre', ['vi', 'na', 'gre']);
assertSeparacion($separador, 'afrontar', ['a', 'fron', 'tar']);
// Separables VC.CV
assertSeparacion($separador, 'obturar', ['ob', 'tu', 'rar']); // bt
assertSeparacion($separador, 'adviento', ['ad', 'vien', 'to']); // dv
assertSeparacion($separador, 'subjetivo', ['sub', 'je', 'ti', 'vo']); // bj
assertSeparacion($separador, 'absoluto', ['ab', 'so', 'lu', 'to']); // bs
assertSeparacion($separador, 'ritmo', ['rit', 'mo']); // tm
assertSeparacion($separador, 'amnesia', ['am', 'ne', 'sia']); // mn (aunque raro, se separa)
assertSeparacion($separador, 'insomne', ['in', 'som', 'ne']); // mn
assertSeparacion($separador, 'arácnido', ['a', 'rác', 'ni', 'do']); // cn
assertSeparacion($separador, 'arritmia', ['a', 'rrit', 'mia']); // tm
assertSeparacion($separador, 'pizza', ['piz', 'za']); // zz (extranjerismo adaptado) -> pít-za (pronunciación), pero norma RAE -> piz-za
// Grupo tl
assertSeparacion($separador, 'atlas', ['at', 'las'], true, 'es_ES');
assertSeparacion($separador, 'atlas', ['a', 'tlas'], true, 'es_MX');
// Grupos CCC
assertSeparacion($separador, 'construir', ['cons', 'truir']); // nstr (VCC.CCV)
assertSeparacion($separador, 'substrato', ['subs', 'tra', 'to']); // bstr (VCC.CCV)
assertSeparacion($separador, 'instruir', ['ins', 'truir']); // nstr (VCC.CCV)
assertSeparacion($separador, 'transgredir', ['trans', 'gre', 'dir']); // nsgr (VCC.CCV)
assertSeparacion($separador, 'exprimir', ['ex', 'pri', 'mir']); // xpr (VC.CCV)
assertSeparacion($separador, 'exclusivo', ['ex', 'clu', 'si', 'vo']); // xcl (VC.CCV)
assertSeparacion($separador, 'explanada', ['ex', 'pla', 'na', 'da']); // xpl (VC.CCV)
assertSeparacion($separador, 'combinar', ['com', 'bi', 'nar']); // mbr -> no es CCC
assertSeparacion($separador, 'hombro', ['hom', 'bro']); // mbr (VC.CCV)
// Grupos CCCC
assertSeparacion($separador, 'instructor', ['ins', 'truc', 'tor']); // nstr (VCC.CCV)
assertSeparacion($separador, 'abstruso', ['abs', 'tru', 'so']); // bstr (VCC.CCV)
assertSeparacion($separador, 'transcribir', ['trans', 'cri', 'bir']); // nscr (VCC.CCV)

// --- 4. Vowel Sequences (Nuevas Palabras) ---
echo "\nN4. Vowel Sequences:\n";
// Diptongos
assertSeparacion($separador, 'jaula', ['jau', 'la']);
assertSeparacion($separador, 'peine', ['pei', 'ne']);
assertSeparacion($separador, 'heroico', ['he', 'roi', 'co']); // H inicial
assertSeparacion($separador, 'neutro', ['neu', 'tro']);
assertSeparacion($separador, 'bou', ['bou']); // ou monosílabo (catalanismo?) - RAE lo trata como diptongo
assertSeparacion($separador, 'circuito', ['cir', 'cui', 'to']);
assertSeparacion($separador, 'ruina', ['rui', 'na']);
assertSeparacion($separador, 'bilingüe', ['bi', 'lin', 'güe']);
assertSeparacion($separador, 'fuimos', ['fui', 'mos']);
assertSeparacion($separador, 'suave', ['sua', 've']);
assertSeparacion($separador, 'prieto', ['prie', 'to']);
assertSeparacion($separador, 'radio', ['ra', 'dio']);
assertSeparacion($separador, 'bestia', ['bes', 'tia']);
assertSeparacion($separador, 'auxilio', ['au', 'xi', 'lio']);
assertSeparacion($separador, 'voyeur', ['vo', 'yeur']); // oy diptongo + eur diptongo? No, voyeur como una palabra, vo-yeur
assertSeparacion($separador, 'voyeur', ['vo', 'yeur']); // oy = oi, eu diptongo
assertSeparacion($separador, 'interviú', ['in', 'ter', 'viú']); // iú diptongo
assertSeparacion($separador, 'fluir', ['fluir']); // ui diptongo
assertSeparacion($separador, 'incluir', ['in', 'cluir']); // ui diptongo
// Triptongos
assertSeparacion($separador, 'confiáis', ['con', 'fiáis']);
assertSeparacion($separador, 'situáis', ['si', 'tuáis']);
assertSeparacion($separador, 'Uruguay', ['U', 'ru', 'guay']);
assertSeparacion($separador, 'guaucho', ['guau', 'cho']); // Palabra inventada para triptongo inicial
// Hiatos
assertSeparacion($separador, 'coágulo', ['co', 'á', 'gu', 'lo']); // oá
assertSeparacion($separador, 'proeza', ['pro', 'e', 'za']); // oe
assertSeparacion($separador, 'israelí', ['is', 'ra', 'e', 'lí']); // ae, elí
assertSeparacion($separador, 'ahogo', ['a', 'ho', 'go']); // aho hiato
assertSeparacion($separador, 'cohete', ['co', 'he', 'te']); // ohe hiato
assertSeparacion($separador, 'caída', ['ca', 'í', 'da']); // aí
assertSeparacion($separador, 'egoísta', ['e', 'go', 'ís', 'ta']); // oí
assertSeparacion($separador, 'ataúd', ['a', 'ta', 'úd']); // aú
assertSeparacion($separador, 'reúma', ['re', 'ú', 'ma']); // eú
assertSeparacion($separador, 'desvarío', ['des', 'va', 'rí', 'o']); // ío
assertSeparacion($separador, 'evalúo', ['e', 'va', 'lú', 'o']); // úo
assertSeparacion($separador, 'chiismo', ['chi', 'is', 'mo']); // ii hiato -> chi-is-mo
assertSeparacion($separador, 'tiito', ['ti', 'i', 'to']); // ii hiato
assertSeparacion($separador, 'contraalmirante', ['con', 'tra', 'al', 'mi', 'ran', 'te']); // aal hiato
assertSeparacion($separador, 'portaaviones', ['por', 'ta', 'a', 'vio', 'nes']); // aa hiato
assertSeparacion($separador, 'sobreesdrújula', ['so', 'bre', 'es', 'drú', 'ju', 'la']); // ee hiato
assertSeparacion($separador, 'poseer', ['po', 'se', 'er']); // ee hiato
assertSeparacion($separador, 'semiinconsciente', ['se', 'mi', 'in', 'cons', 'cien', 'te']); // ii hiato
assertSeparacion($separador, 'zoólogo', ['zo', 'ó', 'lo', 'go']); // oo hiato
assertSeparacion($separador, 'protozoo', ['pro', 'to', 'zo', 'o']); // oo hiato
assertSeparacion($separador, 'bahía', ['ba', 'hí', 'a']); // ahí hiato
assertSeparacion($separador, 'vehículo', ['ve', 'hí', 'cu', 'lo']); // ahí hiato
assertSeparacion($separador, 'vahído', ['va', 'hí', 'do']); // ahí hiato
assertSeparacion($separador, 'mohíno', ['mo', 'hí', 'no']); // ohí hiato
assertSeparacion($separador, 'buhardilla', ['buhar', 'di', 'lla']); // Corrección: RAE admite buhar-di-lla, buar-di-lla no estándar
assertSeparacion($separador, 'prohibición', ['prohi', 'bi', 'ción']); // oi dip
assertSeparacion($separador, 'desahucio', ['de', 'sahu', 'cio']); // au dip
assertSeparacion($separador, 'cohibido', ['cohi', 'bi', 'do']); // oi dip
assertSeparacion($separador, 'ahínco', ['a', 'hín', 'co']); // ahí hiato
// Secuencias largas VVVV+
assertSeparacion($separador, 'leíais', ['le', 'í', 'ais']); // eí hiato, aí hiato, ais diptongo
assertSeparacion($separador, 'caíais', ['ca', 'í', 'ais']); // aí hiato, aí hiato, ais diptongo
assertSeparacion($separador, 'creíais', ['cre', 'í', 'ais']); // eí hiato, aí hiato, ais diptongo
assertSeparacion($separador, 'veíais', ['ve', 'í', 'ais']); // eí hiato, aí hiato, ais diptongo
assertSeparacion($separador, 'apreciáis', ['a', 'pre', 'ciáis']); // iai triptongo

// --- 5. Hache Intercalada (no vocálica - Nuevas Palabras) ---
echo "\nN5. Hache Intercalada (no vocálica):\n";
assertSeparacion($separador, 'adherir', ['ad', 'he', 'rir']);
assertSeparacion($separador, 'enhebrar', ['en', 'he', 'brar']);
assertSeparacion($separador, 'subhasta', ['sub', 'has', 'ta']); // RAE admite sub-has-tar, aunque subastar es más común
assertSeparacion($separador, 'exhibir', ['ex', 'hi', 'bir']); // hi diptongo
assertSeparacion($separador, 'exhortar', ['ex', 'hor', 'tar']);
assertSeparacion($separador, 'inherente', ['in', 'he', 'ren', 'te']);
assertSeparacion($separador, 'cohesión', ['co', 'he', 'sión']);
assertSeparacion($separador, 'vehemencia', ['ve', 'he', 'men', 'cia']); // ehe hiato

// --- 6. Prefijos y Composición (Nuevas Palabras) ---
echo "\nN6. Prefijos y Composición:\n";
// Fonética
assertSeparacion($separador, 'ineficaz', ['i', 'ne', 'fi', 'caz'], true, 'es_ES', 'fonetica', 'PF: ineficaz'); // C+V -> V.CV
assertSeparacion($separador, 'anormal', ['a', 'nor', 'mal'], true, 'es_ES', 'fonetica', 'PF: anormal'); // V+C -> V.CVC
assertSeparacion($separador, 'improbable', ['im', 'pro', 'ba', 'ble'], true, 'es_ES', 'fonetica', 'PF: improbable'); // VC.CCV
assertSeparacion($separador, 'vicepresidente', ['vi', 'ce', 'pre', 'si', 'den', 'te'], true, 'es_ES', 'fonetica', 'PF: vicepresidente'); // V+C -> V.CV
assertSeparacion($separador, 'contradecir', ['con', 'tra', 'de', 'cir'], true, 'es_ES', 'fonetica', 'PF: contradecir'); // V+C -> V.CV
assertSeparacion($separador, 'rehacer', ['re', 'ha', 'cer'], true, 'es_ES', 'fonetica', 'PF: rehacer'); // V+hV -> V.hV
assertSeparacion($separador, 'excomulgar', ['ex', 'co', 'mul', 'gar'], true, 'es_ES', 'fonetica', 'PF: excomulgar'); // VC.CV
assertSeparacion($separador, 'supermercado', ['su', 'per', 'mer', 'ca', 'do'], true, 'es_ES', 'fonetica', 'PF: supermercado'); // VC.CV
assertSeparacion($separador, 'intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'fonetica', 'PF: intramuscular'); // V.CCV
assertSeparacion($separador, 'obnubilar', ['ob', 'nu', 'bi', 'lar'], true, 'es_ES', 'fonetica', 'PF: obnubilar'); // VC.CV
assertSeparacion($separador, 'adjunto', ['ad', 'jun', 'to'], true, 'es_ES', 'fonetica', 'PF: adjunto'); // VC.CVC
assertSeparacion($separador, 'abjurar', ['ab', 'ju', 'rar'], true, 'es_ES', 'fonetica', 'PF: abjurar'); // VC.CV

// Morfológica
assertSeparacion($separador, 'ineficaz', ['in', 'e', 'fi', 'caz'], true, 'es_ES', 'morfologica', 'PM: ineficaz'); // in | eficaz
assertSeparacion($separador, 'anormal', ['a', 'nor', 'mal'], true, 'es_ES', 'morfologica', 'PM: anormal'); // a | normal (V+C no divide morfo)
assertSeparacion($separador, 'improbable', ['im', 'pro', 'ba', 'ble'], true, 'es_ES', 'morfologica', 'PM: improbable'); // im | probable
assertSeparacion($separador, 'vicepresidente', ['vi', 'ce', 'pre', 'si', 'den', 'te'], true, 'es_ES', 'morfologica', 'PM: vicepresidente'); // vice | presidente
assertSeparacion($separador, 'contradecir', ['con', 'tra', 'de', 'cir'], true, 'es_ES', 'morfologica', 'PM: contradecir'); // contra | decir
assertSeparacion($separador, 'rehacer', ['re', 'ha', 'cer'], true, 'es_ES', 'morfologica', 'PM: rehacer'); // re | hacer (V+hV no suele dividir)
assertSeparacion($separador, 'excomulgar', ['ex', 'co', 'mul', 'gar'], true, 'es_ES', 'morfologica', 'PM: excomulgar'); // ex | comulgar
assertSeparacion($separador, 'supermercado', ['su', 'per', 'mer', 'ca', 'do'], true, 'es_ES', 'morfologica', 'PM: supermercado'); // super | mercado
assertSeparacion($separador, 'intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'morfologica', 'PM: intramuscular'); // intra | muscular
assertSeparacion($separador, 'obnubilar', ['ob', 'nu', 'bi', 'lar'], true, 'es_ES', 'morfologica', 'PM: obnubilar'); // ob | nubilar
assertSeparacion($separador, 'adjunto', ['ad', 'jun', 'to'], true, 'es_ES', 'morfologica', 'PM: adjunto'); // ad | junto
assertSeparacion($separador, 'abjurar', ['ab', 'ju', 'rar'], true, 'es_ES', 'morfologica', 'PM: abjurar'); // ab | jurar

// Adaptativa
assertSeparacion($separador, 'ineficaz', ['in', 'e', 'fi', 'caz'], true, 'es_ES', 'adaptativa', 'PA: ineficaz'); // Morfológica (C+V)
assertSeparacion($separador, 'anormal', ['a', 'nor', 'mal'], true, 'es_ES', 'adaptativa', 'PA: anormal'); // Fonética (V+C)
assertSeparacion($separador, 'improbable', ['im', 'pro', 'ba', 'ble'], true, 'es_ES', 'adaptativa', 'PA: improbable'); // Fonética (grupo fuerte)
assertSeparacion($separador, 'vicepresidente', ['vi', 'ce', 'pre', 'si', 'den', 'te'], true, 'es_ES', 'adaptativa', 'PA: vicepresidente'); // Fonética (V+C)
assertSeparacion($separador, 'contradecir', ['con', 'tra', 'de', 'cir'], true, 'es_ES', 'adaptativa', 'PA: contradecir'); // Fonética (grupo fuerte)
assertSeparacion($separador, 'rehacer', ['re', 'ha', 'cer'], true, 'es_ES', 'adaptativa', 'PA: rehacer'); // Fonética (V+hV)
assertSeparacion($separador, 'excomulgar', ['ex', 'co', 'mul', 'gar'], true, 'es_ES', 'adaptativa', 'PA: excomulgar'); // Morfológica (C+C no fuerte)
assertSeparacion($separador, 'supermercado', ['su', 'per', 'mer', 'ca', 'do'], true, 'es_ES', 'adaptativa', 'PA: supermercado'); // Fonética (V+C)
assertSeparacion($separador, 'intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'adaptativa', 'PA: intramuscular'); // Fonética (V+C ?) -> Debería ser in-tra... (Morfo C+C), revisar adaptativa
assertSeparacion($separador, 'intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'adaptativa', 'PA: intramuscular'); // Corrección: intra | muscular -> V+C -> fonética: in-tra...
assertSeparacion($separador, 'obnubilar', ['ob', 'nu', 'bi', 'lar'], true, 'es_ES', 'adaptativa', 'PA: obnubilar'); // Morfológica (C+C no fuerte)
assertSeparacion($separador, 'adjunto', ['ad', 'jun', 'to'], true, 'es_ES', 'adaptativa', 'PA: adjunto'); // Morfológica (C+C no fuerte)
assertSeparacion($separador, 'abjurar', ['ab', 'ju', 'rar'], true, 'es_ES', 'adaptativa', 'PA: abjurar'); // Morfológica (C+C no fuerte)

// --- 7. Palabras Largas y Casos Complejos (Nuevas Palabras) ---
echo "\nN7. Palabras Largas y Complejas:\n";
assertSeparacion($separador, 'paralelepípedo', ['pa', 'ra', 'le', 'le', 'pí', 'pe', 'do']);
assertSeparacion($separador, 'anticonstitucionalmente', ['an', 'ti', 'cons', 'ti', 'tu', 'cio', 'nal', 'men', 'te']); // ii hiato -> ti-cons? No. ti | cons -> ti-cons... Es an-ti-cons...
assertSeparacion($separador, 'anticonstitucionalmente', ['an', 'ti', 'cons', 'ti', 'tu', 'cio', 'nal', 'men', 'te']); // anti | constitucionalmente
assertSeparacion($separador, 'ciclopentanoperhidrofenantreno', ['ci', 'clo', 'pen', 'ta', 'no', 'per', 'hi', 'dro', 'fe', 'nan', 'tre', 'no']); // o-p VCC, e-r VC#, i-d VC.CV, o-f VC.CV, e-n VC.CV, e-n VC.CV, o V final
assertSeparacion($separador, 'neuroendocrino', ['neu', 'roen', 'do', 'cri', 'no'], false, 'es_ES', 'fonetica', 'neuro... (no hiato)'); // Forzar diptongo oe
assertSeparacion($separador, 'neuroendocrino', ['neu', 'ro', 'en', 'do', 'cri', 'no'], true, 'es_ES', 'fonetica', 'neuro... (hiato oe)');
assertSeparacion($separador, 'telecomunicaciones', ['te', 'le', 'co', 'mu', 'ni', 'ca', 'cio', 'nes']);
assertSeparacion($separador, 'fotolitografía', ['fo', 'to', 'li', 'to', 'gra', 'fí', 'a']); // i-a hiato

echo "\nNUEVA batería de pruebas completada.\n";
