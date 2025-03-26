# Separador de Sílabas RAE (PHP) 🇪🇸

[![PHP Version Require](http://poser.pugx.org/kuasarx/separador-silabas-rae/require/php)](https://packagist.org/packages/kuasarx/separador-silabas-rae)
[![Latest Stable Version](http://poser.pugx.org/kuasarx/separador-silabas-rae/v)](https://packagist.org/packages/kuasarx/separador-silabas-rae)
[![License](http://poser.pugx.org/kuasarx/separador-silabas-rae/license)](https://packagist.org/packages/kuasarx/separador-silabas-rae)
[![Total Downloads](https://img.shields.io/packagist/dt/kuasarx/separador-silabas-rae.svg?style=flat-square)](https://packagist.org/packages/kuasarx/separador-silabas-rae)

<p align="center">
  <img src="https://img.shields.io/badge/RAE-Ortografía_2010-blue?style=for-the-badge" alt="Basado en RAE Ortografía 2010">
</p>

**SeparadorSilabasRAE** es una librería PHP diseñada para dividir palabras del idioma español en sus sílabas constituyentes, siguiendo estrictamente las **reglas oficiales** publicadas por la Real Academia Española (RAE) en su Ortografía de 2010.

**Características Principales:**

*   ✅ **Cumplimiento RAE:** Implementa las reglas fundamentales de división silábica, incluyendo:
    *   Consonantes entre vocales (`ca-sa`).
    *   Grupos consonánticos inseparables (`bl`, `pr`, `fl`... -> `ha-blar`).
    *   Grupos consonánticos separables (`st`, `ct`, `gn`... -> `cos-ta`).
    *   Secuencias de 3 y 4 consonantes (`ins-pi-rar`, `abs-trac-to`).
*   🗣️ **Fonética Avanzada:**
    *   **Diptongos:** Reconoce y mantiene unidos los diptongos (`ciu-dad`, `pei-ne`, `vein-tiún`).
    *   **Triptongos:** Reconoce y mantiene unidos los triptongos (`lim-piáis`, `U-ru-guay`).
    *   **Hiatos:** Separa correctamente los hiatos, tanto por vocales fuertes como por débiles acentuadas y vocales idénticas (`le-er`, `pa-ís`, `chi-i-ta`, `Ra-úl`). Opción para *no* separar hiatos (uso no estándar).
    *   **Letra H:** Maneja la 'h' intercalada correctamente, tanto en diptongos/hiatos (`ahu-mar`, `pro-hí-be`) como entre consonantes (`des-ha-cer`, `in-há-bil`, `su-per-hom-bre`).
    *   **Letra Y:** Trata la 'y' final como vocal 'i' (`rey`, `ley`).
    *   **Dígrafos:** Considera `ch`, `ll`, `rr` como unidades inseparables (`co-che`, `ca-lle`, `pe-rro`).
    *   **Grupos `qu`/`gu`:** Maneja `que`/`qui` y `gue`/`gui` fonéticamente.
    *   **Letra X:** Gestiona la `x` intervocálica (`e-xa-men`) y en otras posiciones (`ex-tra-ño`).
*   🌎 **Variantes Regionales:** Soporte inicial para variantes dialectales como el grupo `tl`:
    *   `es_ES` (España, default): `at-le-ta`
    *   `es_MX` (México y zonas de América): `a-tle-ta`
*   🧩 **Estrategias de Prefijos:** Ofrece flexibilidad al dividir palabras con prefijos:
    *   `fonetica` (default): Prioriza la división fonética estándar (`su-bra-yar`, `in-há-bil`, `e-xa-lum-no`).
    *   `morfologica`: Intenta separar el prefijo del resto de la palabra (`sub-ra-yar`, `in-há-bil`, `ex-a-lum-no`).
    *   `adaptativa`: Usa heurísticas para decidir entre fonética y morfológica según el contexto (`su-bra-yar` vs `sub-lu-nar`).
*   ⚡ **Optimización:** Incluye una caché LRU (Least Recently Used) interna para acelerar la separación de palabras frecuentes.
*   🐛 **Depuración:** Ofrece un modo de rastreo opcional (`enableTracing()`) para visualizar el proceso lógico paso a paso.
*   📦 **Instalación Fácil:** Disponible vía Composer.
*   ✨ **Código Moderno:** Escrito en PHP moderno con tipado estricto (`declare(strict_types=1)`).

## Instalación

La forma recomendada de instalar la librería es a través de [Composer](https://getcomposer.org/):

```bash
composer require kuasarx/separador-silabas-rae
```

Asegúrate de tener la extensión `mbstring` de PHP habilitada, ya que es necesaria para el manejo correcto de caracteres multibyte.

## Uso Básico

```php
<?php

require 'vendor/autoload.php'; // Si usas Composer

use Kuasarx\Linguistica\SeparadorSilabasRAE;
use Kuasarx\Linguistica\InvalidWordException;

$separador = new SeparadorSilabasRAE();

try {
    // --- Ejemplo Simple ---
    $palabra = "constitucionalidad";
    $resultado = $separador->separar($palabra);

    echo "Palabra: " . $palabra . "\n";
    echo "Sílabas: " . implode('-', $resultado['silabas']) . "\n";
    // Salida: Sílabas: cons-ti-tu-cio-na-li-dad

    // --- Ejemplo con Hiato ---
    $palabraHiato = "aéreo";
    $resultadoHiato = $separador->separar($palabraHiato);
    echo "\nPalabra: " . $palabraHiato . "\n";
    echo "Sílabas: " . implode('-', $resultadoHiato['silabas']) . "\n";
    // Salida: Sílabas: a-é-re-o

    // --- Ejemplo con H ---
    $palabraH = "deshacer";
    $resultadoH = $separador->separar($palabraH);
    echo "\nPalabra: " . $palabraH . "\n";
    echo "Sílabas: " . implode('-', $resultadoH['silabas']) . "\n";
    // Salida: Sílabas: des-ha-cer

    // --- Variante Regional (México) ---
    $palabraTL = "atleta";
    $resultadoTL_MX = $separador->separar($palabraTL, true, 'es_MX');
    echo "\nPalabra: " . $palabraTL . " (Región MX)\n";
    echo "Sílabas: " . implode('-', $resultadoTL_MX['silabas']) . "\n";
    // Salida: Sílabas: a-tle-ta

    // --- Estrategia Morfológica ---
    $palabraPrefijo = "subrayar";
    $resultadoPrefijoMorf = $separador->separar($palabraPrefijo, true, 'es_ES', 'morfologica');
    echo "\nPalabra: " . $palabraPrefijo . " (Estrategia Morfológica)\n";
    echo "Sílabas: " . implode('-', $resultadoPrefijoMorf['silabas']) . "\n";
    // Salida: Sílabas: sub-ra-yar

    // --- Estrategia Adaptativa ---
    $palabraPrefijoAdapt = "sublunar";
    $resultadoPrefijoAdapt = $separador->separar($palabraPrefijoAdapt, true, 'es_ES', 'adaptativa');
    echo "\nPalabra: " . $palabraPrefijoAdapt . " (Estrategia Adaptativa)\n";
    echo "Sílabas: " . implode('-', $resultadoPrefijoAdapt['silabas']) . "\n";
    // Salida: Sílabas: sub-lu-nar

    // --- Ignorar Hiatos ---
    $palabraPais = "país";
    $resultadoPaisNoHiato = $separador->separar($palabraPais, false); // hiatos = false
    echo "\nPalabra: " . $palabraPais . " (Sin Hiatos)\n";
    echo "Sílabas: " . implode('-', $resultadoPaisNoHiato['silabas']) . "\n";
    // Salida: Sílabas: pais

    // --- Generar HTML ---
    $resultadoHTML = $separador->separar("programación");
    echo "\nHTML para 'programación':\n";
    echo $separador->generarHtml($resultadoHTML['silabas']) . "\n";
    // Salida: <span class="silaba">pro</span><span class="silaba">gra</span><span class="silaba">ma</span><span class="silaba">ción</span>

} catch (InvalidWordException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
```

## Documentación de la Clase

### `SeparadorSilabasRAE`

#### Métodos Públicos

*   `__construct()`: Constructor (no requiere parámetros).
*   `separar(string $palabra, bool $incluirHiatos = true, string $regionalismo = 'es_ES', string $estrategiaPrefijos = 'fonetica'): array`:
    *   Método principal para realizar la separación.
    *   **Parámetros:**
        *   `$palabra` (string): La palabra a separar.
        *   `$incluirHiatos` (bool, opcional, default: `true`): Si `true`, separa los hiatos; si `false`, los trata como diptongos.
        *   `$regionalismo` (string, opcional, default: `'es_ES'`): Código regional (`'es_ES'` o `'es_MX'`) para variantes como `tl`.
        *   `$estrategiaPrefijos` (string, opcional, default: `'fonetica'`): Estrategia para prefijos (`'fonetica'`, `'morfologica'`, `'adaptativa'`).
    *   **Retorna:** Un array asociativo con:
        *   `silabas` (array): Las sílabas resultantes.
        *   `puntos_division` (array): Índices donde se puede dividir con guion.
        *   `excepciones_aplicadas` (array): Log de reglas especiales usadas.
        *   `tiempo_ms` (float): Tiempo de ejecución en milisegundos.
        *   `fuente` (string): `'cache'` o `'calculado'`.
        *   `trace` (array, opcional): Log de rastreo si está habilitado.
    *   **Lanza:** `InvalidWordException` si la palabra es inválida.
*   `generarHtml(array $silabas): string`:
    *   Convierte un array de sílabas en una cadena HTML con `<span class="silaba">`.
    *   **Parámetro:** `$silabas` (array): El array de sílabas.
    *   **Retorna:** (string) La cadena HTML.
*   `enableTracing(bool $enable = true): void`:
    *   Habilita (`true`) o deshabilita (`false`) el log de rastreo detallado. Útil para depuración.
*   `static limpiarCache(): void`:
    *   Vacía la caché interna de resultados.
*   `static getCacheSize(): int`:
    *   Retorna el número actual de elementos almacenados en la caché.

#### Excepciones

*   `InvalidWordException`: Se lanza si la palabra proporcionada a `separar()` está vacía o contiene caracteres no permitidos (diferentes a letras del alfabeto español, incluyendo ñ, ü y tildes).

## Detalles de Implementación y Reglas RAE

La clase implementa las reglas descritas en la Ortografía de la RAE (2010), § 4.1.1.1.1.2:

*   **Núcleo Vocálico:** Identifica diptongos (incl. `iú`, `uí`), triptongos e hiatos (incl. `F+F`, `F+Da`, `Da+F`, `Da+Da`, `VV idénticas`).
*   **Combinaciones Consonánticas:**
    *   **V C V:** `V.CV` (ca-sa)
    *   **V CC V:**
        *   Grupo inseparable (pr, bl...): `V.CCV` (a-brir)
        *   Grupo `tl`: `Vt.lV` (at-las) en `es_ES`, `V.tlV` (a-tlas) en `es_MX`.
        *   Otros: `VC.CV` (ac-to, ap-to, cons-ta, des-ha-cer)
    *   **V CCC V:**
        *   Si últimas dos son inseparables: `VC.CCV` (des-pre-cio)
        *   Otros: `VCC.CV` (ins-tan-te, pers-pi-caz)
    *   **V CCCC V:** `VCC.CCV` (abs-trac-to, cons-tre-ñir)
*   **Letra H:**
    *   No impide diptongos/hiatos (ahu-mar, pro-hí-be, a-ho-ra).
    *   Si está entre consonante y vocal, va con la vocal siguiente (des-ha-cer, in-há-bil, su-per-hom-bre).
*   **Letra X:**
    *   Entre vocales: `V.xV` (e-xa-men).
    *   Inicio de palabra: `X=V` (xe-no-fo-bia).
    *   Fin de sílaba: `VC.CV` (ex-tra-ño).
*   **Prefijos:** Las estrategias `morfologica` y `adaptativa` intentan respetar los límites morfológicos basándose en una lista de prefijos comunes y heurísticas sobre las consonantes/vocales en el punto de unión y la fuerza de los grupos consonánticos formados.

## Pruebas

La librería incluye un extenso conjunto de pruebas unitarias utilizando PHPUnit. Puedes ejecutarlas con:

```bash
composer test
```

O para ver la cobertura de código:

```bash
composer test-coverage
```

(Esto generará un informe en el directorio `coverage/`).

## Contribuciones

Las contribuciones son bienvenidas. Por favor, si encuentras un error o tienes una sugerencia:

1.  Verifica si ya existe un *issue* similar.
2.  Si no, crea un nuevo *issue* detallando el problema o la propuesta.
3.  Para *pull requests*, asegúrate de que las pruebas unitarias sigan pasando y, si es posible, añade nuevas pruebas para cubrir tu cambio.

## Licencia

Este proyecto está bajo la Licencia MIT.

---

Creado por Juan Camacho ([kuasarx@gmail.com](mailto:kuasarx@gmail.com))
```
