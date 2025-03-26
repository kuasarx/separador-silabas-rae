<?php

declare(strict_types=1);

namespace Kuasarx\Linguistica\Tests;

use PHPUnit\Framework\TestCase;
use Kuasarx\Linguistica\SeparadorSilabasRAE;
use Kuasarx\Linguistica\InvalidWordException;

/**
 * Pruebas Unitarias para la clase SeparadorSilabasRAE.
 *
 * @covers \Kuasarx\Linguistica\SeparadorSilabasRAE
 */
final class SeparadorSilabasRAETest extends TestCase
{
    private SeparadorSilabasRAE $separador;

    /**
     * Configuración antes de cada test.
     */
    protected function setUp(): void
    {
        $this->separador = new SeparadorSilabasRAE();
        // Limpiar caché antes de cada test para aislamiento
        SeparadorSilabasRAE::limpiarCache();
    }

    /**
     * @dataProvider provideCasosGenerales
     * @dataProvider provideCasosHiatosDiptongosTriptongos
     * @dataProvider provideCasosHache
     * @dataProvider provideCasosGruposConsonanticos
     * @dataProvider provideCasosPrefijosFonetica
     * @dataProvider provideCasosPrefijosMorfologica
     * @dataProvider provideCasosPrefijosAdaptativa
     * @dataProvider provideCasosRegionales
     * @dataProvider provideCasosLimite
     */
    public function testSeparacionCorrecta(
        string $palabra,
        array $esperadoSilabas,
        bool $hiatos = true,
        string $region = 'es_ES',
        string $prefijos = 'fonetica',
        string $mensaje = ''
    ): void {
        $resultado = $this->separador->separar($palabra, $hiatos, $region, $prefijos);

        $this->assertEquals(
            $esperadoSilabas,
            $resultado['silabas'],
            "Fallo en '{$palabra}' ({$mensaje})"
        );

        // Opcional: Verificar puntos de división calculándolos desde las sílabas esperadas
        $esperadoPuntos = [];
        $indice = 0;
        for ($i = 0; $i < count($esperadoSilabas) - 1; $i++) {
            $indice += mb_strlen($esperadoSilabas[$i], 'UTF-8');
            $esperadoPuntos[] = $indice;
        }
        $this->assertEquals(
            $esperadoPuntos,
            $resultado['puntos_division'],
            "Fallo en puntos_division para '{$palabra}' ({$mensaje})"
        );
    }

    // -------------------------------------------------------------------------
    // Data Providers
    // -------------------------------------------------------------------------

    public static function provideCasosGenerales(): array
    {
        return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'Monosílabo Vocal' => ['a', ['a']],
            'Monosílabo CVC (sol)' => ['sol', ['sol']],
            'Monosílabo CCVC (tren)' => ['tren', ['tren']],
            'Monosílabo CVC (pan)' => ['pan', ['pan']],
            'Monosílabo CVC (luz)' => ['luz', ['luz']],
            'Monosílabo VC (es)' => ['es', ['es']],
            'Monosílabo VC (un)' => ['un', ['un']],
            'Monosílabo CV (no)' => ['no', ['no']],
            'Monosílabo CV (y yo)' => ['yo', ['yo']],
            'Monosílabo CV (y ya)' => ['ya', ['ya']],
            'Básico VCV (casa)' => ['casa', ['ca', 'sa']],
            'Básico VCV (mapa)' => ['mapa', ['ma', 'pa']],
            'Básico CCV (libro)' => ['li', 'bro'],
            'Básico VC final (comer)' => ['comer', ['co', 'mer']],
            'Básico CVC final (cantar)' => ['cantar', ['can', 'tar']],
            'Inicio V (amigo)' => ['amigo', ['a', 'mi', 'go']],
            'Inicio V polisílaba (elefante)' => ['elefante', ['e', 'le', 'fan', 'te']],
            'Monosílabo CVC (mar)' => ['mar', ['mar']],
            'Monosílabo CCVC (club)' => ['club', ['club']],
            'Monosílabo CVCC (vals)' => ['vals', ['vals']],
            'Monosílabo CVC (vid)' => ['vid', ['vid']],
            'Monosílabo CVC (red)' => ['red', ['red']],
            'Monosílabo CVC (dos)' => ['dos', ['dos']],
            'Monosílabo CV(y) (voy)' => ['voy', ['voy']],
            'Monosílabo CV(y) (ley)' => ['ley', ['ley']],
            'Básico VCV (mesa)' => ['mesa', ['me', 'sa']],
            'Básico VCV (luna)' => ['lu', 'na']],
            'Básico V.CCVC (abrir)' => ['abrir', ['a', 'brir']],
            'Básico VC final (vivir)' => ['vivir', ['vi', 'vir']],
            'Básico CVC final (jugar)' => ['jugar', ['ju', 'gar']],
            'Inicio VC (objeto)' => ['objeto', ['ob', 'je', 'to']],
            'Inicio VC (isla)' => ['isla', ['is', 'la']],
            'Inicio VC (urna)' => ['urna', ['ur', 'na']],
        ];
    }

    public static function provideCasosHiatosDiptongosTriptongos(): array
    {
         return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'Idea (hiato i-d-ea)' => ['idea', ['i', 'de', 'a']],
            'Oasis (hiato o-a)' => ['oasis', ['o', 'a', 'sis']],
            'Diptongo ue (puede)' => ['puede', ['pue', 'de']],
            'Diptongo ai (aire)' => ['aire', ['ai', 're']],
            'Diptongo iu (ciudad)' => ['ciudad', ['ciu', 'dad']],
            'Monosílabo y final (rey)' => ['rey', ['rey']],
            'Triptongo uau (guau)' => ['guau', ['guau']],
            'Triptongo uái (averiguáis)' => ['averiguáis', ['a', 've', 'ri', 'guáis']],
            'Hiato í-o (río)' => ['río', ['rí', 'o']],
            'Hiato e-e (leer)' => ['leer', ['le', 'er']],
            'Hiato o-o (cooperar)' => ['cooperar', ['co', 'o', 'pe', 'rar']],
            'Hiato í-a (día)' => ['día', ['dí', 'a']],
            'Hiato a-ú (baúl)' => ['baúl', ['ba', 'úl']],
            'Hiato ignorado (pais)' => ['pais', ['pais'], false], // pais con hiato=false
            'Hiato a-ó (caótico)' => ['caótico', ['ca', 'ó', 'ti', 'co']],
            'Monosílabo RAE 2010 (guion)' => ['guion', ['guion']],
            'Monosílabo RAE 2010 h (truhan)' => ['truhan', ['truhan']],
            'Diptongo iú tilde (veintiún)' => ['veintiún', ['vein', 'tiún']],
            'Diptongo ui (jesuita)' => ['jesuita', ['je', 'sui', 'ta']],
            'Hiato ii (chiita)' => ['chiita', ['chi', 'i', 'ta']],
            'Hiato uu (duunviro)' => ['duunviro', ['du', 'un', 'vi', 'ro']],
            'Hiato ií (chií)' => ['chií', ['chi', 'í']],
            'Hiato ií (friísimo)' => ['friísimo', ['fri', 'í', 'si', 'mo']],
            'Hiato oí (oír)' => ['oír', ['o', 'ír']],
            'Hiato eí (reír)' => ['reír', ['re', 'ír']],
            'Hiato aú (laúd)' => ['laúd', ['la', 'úd']],
            'Hiato eú (reúne)' => ['reúne', ['re', 'ú', 'ne']],
            'Hiato ío (frío)' => ['frío', ['frí', 'o']],
            'Hiato úo (continúo)' => ['continúo', ['con', 'ti', 'nú', 'o']],
            'Diptongo au (jaula)' => ['jaula', ['jau', 'la']],
            'Diptongo ei (peine)' => ['peine', ['pei', 'ne']],
            'Diptongo oi (heroico)' => ['heroico', ['he', 'roi', 'co']],
            'Diptongo eu (neutro)' => ['neutro', ['neu', 'tro']],
            'Diptongo ou (bou)' => ['bou', ['bou']],
            'Diptongo ui (circuito)' => ['circuito', ['cir', 'cui', 'to']],
            'Diptongo ui (ruina)' => ['ruina', ['rui', 'na']],
            'Diptongo üe (bilingüe)' => ['bilingüe', ['bi', 'lin', 'güe']],
            'Diptongo ui (fuimos)' => ['fuimos', ['fui', 'mos']],
            'Diptongo ua (suave)' => ['suave', ['sua', 've']],
            'Diptongo ie (prieto)' => ['prieto', ['prie', 'to']],
            'Diptongo io (radio)' => ['radio', ['ra', 'dio']],
            'Diptongo ia (bestia)' => ['bestia', ['bes', 'tia']],
            'Diptongo io (auxilio)' => ['auxilio', ['au', 'xi', 'lio']], // au, io
            'Diptongo VOYEUR' => ['voyeur', ['vo', 'yeur']], // oy=oi, eu dip
            'Diptongo iú (interviú)' => ['interviú', ['in', 'ter', 'viú']],
            'Diptongo ui (fluir)' => ['fluir', ['fluir']],
            'Diptongo ui (incluir)' => ['incluir', ['in', 'cluir']],
            'Triptongo iái (confiáis)' => ['confiáis', ['con', 'fiáis']],
            'Triptongo uái (situáis)' => ['situáis', ['si', 'tuáis']],
            'Triptongo uay (Uruguay)' => ['Uruguay', ['U', 'ru', 'guay']],
            'Secuencia iei (vieira)' => ['vieira', ['viei', 'ra']], // iei no es triptongo
            'Secuencia ioi (hioides)' => ['hioides', ['hioi', 'des']],
            'Hiato oá (coágulo)' => ['coágulo', ['co', 'á', 'gu', 'lo']],
            'Hiato oe (proeza)' => ['proeza', ['pro', 'e', 'za']],
            'Hiato ae/elí (israelí)' => ['israelí', ['is', 'ra', 'e', 'lí']],
            'Hiato ii (tiito)' => ['tiito', ['ti', 'i', 'to']],
            'Hiato aa (contraalmirante)' => ['contraalmirante', ['con', 'tra', 'al', 'mi', 'ran', 'te']],
            'Hiato aa (portaaviones)' => ['portaaviones', ['por', 'ta', 'a', 'vio', 'nes']],
            'Hiato ee (sobreesdrújula)' => ['sobreesdrújula', ['so', 'bre', 'es', 'drú', 'ju', 'la']],
            'Hiato ee (poseer)' => ['poseer', ['po', 'se', 'er']],
            'Hiato ii (semiinconsciente)' => ['semiinconsciente', ['se', 'mi', 'in', 'cons', 'cien', 'te']], // ii hiato, ie dip
            'Hiato oo (zoólogo)' => ['zoólogo', ['zo', 'ó', 'lo', 'go']],
            'Hiato oo (protozoo)' => ['protozoo', ['pro', 'to', 'zo', 'o']],
            'VVVV leíais' => ['leíais', ['le', 'í', 'ais']],
            'VVVV caíais' => ['caíais', ['ca', 'í', 'ais']],
            'VVVV creíais' => ['creíais', ['cre', 'í', 'ais']],
            'VVVV veíais' => ['veíais', ['ve', 'í', 'ais']],
            'VVV apreciáis (triptongo)' => ['apreciáis', ['a', 'pre', 'ciáis']],
         ];
    }

    public static function provideCasosHache(): array
    {
        return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'Hiato a-hí (ahí)' => ['ahí', ['a', 'hí']],
            'Hiato bú-ho (búho)' => ['búho', ['bú', 'ho']],
            'Hiato re-hén (rehén)' => ['rehén', ['re', 'hén']],
            'Hiato alco-hol (alcohol)' => ['alcohol', ['al', 'co', 'hol']],
            'Hiato co-he-te (cohete)' => ['cohete', ['co', 'he', 'te']],
            'Hiato pro-hí-be (prohíbe)' => ['prohíbe', ['pro', 'hí', 'be']],
            'Diptongo a-hi-jado (ahijado)' => ['ahijado', ['ahi', 'ja', 'do']],
            'Diptongo a-hu-mado (ahumado)' => ['ahumado', ['ahu', 'ma', 'do']],
            'Diptongo sahu-merio (sahumerio)' => ['sahumerio', ['sahu', 'me', 'rio']],
            'Diptongo rehu-sar (rehusar)' => ['rehusar', ['rehu', 'sar']],
            'VC.hV (deshacer)' => ['deshacer', ['des', 'ha', 'cer']],
            'VC.hV (deshielo)' => ['deshielo', ['des', 'hie', 'lo']],
            'VC.hV (deshonra)' => ['deshonra', ['des', 'hon', 'ra']],
            'VC.hV (anhelo)' => ['anhelo', ['an', 'he', 'lo']],
            'VC.hV (alhaja)' => ['alhaja', ['al', 'ha', 'ja']],
            'VC.hV (inhábil)' => ['inhábil', ['in', 'há', 'bil']],
            'VC.hV (inhibir)' => ['inhibir', ['in', 'hi', 'bir']],
            'VC.hV (exhalar)' => ['exhalar', ['ex', 'ha', 'lar']],
            'VC.hV (exhausto)' => ['exhausto', ['ex', 'haus', 'to']],
            'VC.hV (adhesión)' => ['adhesión', ['ad', 'he', 'sión']],
            'VC.hV (subhumano)' => ['subhumano', ['sub', 'hu', 'ma', 'no']],
            'VC.hV (enhebrar)' => ['enhebrar', ['en', 'he', 'brar']],
            'VC.hV (subhasta)' => ['subhasta', ['sub', 'has', 'ta']],
            'VC.hV (exhibir)' => ['exhibir', ['ex', 'hi', 'bir']],
            'VC.hV (exhortar)' => ['exhortar', ['ex', 'hor', 'tar']],
            'VC.hV (inherente)' => ['inherente', ['in', 'he', 'ren', 'te']],
            'Hiato vehemencia' => ['vehemencia', ['ve', 'he', 'men', 'cia']], // e-h-e -> hiato
            'Diptongo buhardilla' => ['buhardilla', ['bu', 'har', 'di', 'lla']], // RAE: buhar-di-lla (aunque buar-dilla también se oye)
        ];
    }

     public static function provideCasosGruposConsonanticos(): array
    {
        return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            // Inseparables
            'Grupo pl (aplicar)' => ['aplicar', ['a', 'pli', 'car']],
            'Grupo bl (hablar)' => ['hablar', ['ha', 'blar']],
            'Grupo cl (incluir)' => ['incluir', ['in', 'cluir']],
            'Grupo gl (reglamento)' => ['reglamento', ['re', 'gla', 'men', 'to']],
            'Grupo fl (afluente)' => ['afluente', ['a', 'fluen', 'te']],
            'Grupo pr (aprisa)' => ['aprisa', ['a', 'pri', 'sa']],
            'Grupo br (abrigo)' => ['abrigo', ['a', 'bri', 'go']],
            'Grupo tr (letrado)' => ['letrado', ['le', 'tra', 'do']],
            'Grupo dr (ajedrez)' => ['ajedrez', ['a', 'je', 'drez']],
            'Grupo cr (recreo)' => ['recreo', ['re', 'cre', 'o']],
            'Grupo gr (vinagre)' => ['vinagre', ['vi', 'na', 'gre']],
            'Grupo fr (afrontar)' => ['afrontar', ['a', 'fron', 'tar']],
            // Separables
            'Grupo bt (obturar)' => ['obturar', ['ob', 'tu', 'rar']],
            'Grupo dv (adviento)' => ['adviento', ['ad', 'vien', 'to']],
            'Grupo bj (subjetivo)' => ['subjetivo', ['sub', 'je', 'ti', 'vo']],
            'Grupo bs (absoluto)' => ['absoluto', ['ab', 'so', 'lu', 'to']],
            'Grupo tm (ritmo)' => ['ritmo', ['rit', 'mo']],
            'Grupo mn (amnesia)' => ['amnesia', ['am', 'ne', 'sia']],
            'Grupo mn (insomne)' => ['insomne', ['in', 'som', 'ne']],
            'Grupo cn (arácnido)' => ['arácnido', ['a', 'rác', 'ni', 'do']],
            'Grupo tm (arritmia)' => ['arritmia', ['a', 'rrit', 'mia']],
            'Grupo zz (pizza)' => ['pizza', ['piz', 'za']],
            // tl
            'Grupo tl (atlas ES)' => ['atlas', ['at', 'las'], true, 'es_ES'],
            'Grupo tl (atlas MX)' => ['atlas', ['a', 'tlas'], true, 'es_MX'],
            // CCC
            'Grupo nstr (construir)' => ['construir', ['cons', 'truir']],
            'Grupo bstr (substrato)' => ['substrato', ['subs', 'tra', 'to']],
            'Grupo nstr (instruir)' => ['instruir', ['ins', 'truir']],
            'Grupo nsgr (transgredir)' => ['transgredir', ['trans', 'gre', 'dir']],
            'Grupo xpr (exprimir)' => ['exprimir', ['ex', 'pri', 'mir']],
            'Grupo xcl (exclusivo)' => ['exclusivo', ['ex', 'clu', 'si', 'vo']],
            'Grupo xpl (explanada)' => ['explanada', ['ex', 'pla', 'na', 'da']],
            'Grupo mbr (hombro)' => ['hombro', ['hom', 'bro']],
             // CCCC
            'Grupo nstr (instructor)' => ['instructor', ['ins', 'truc', 'tor']],
            'Grupo bstr (abstruso)' => ['abstruso', ['abs', 'tru', 'so']],
            'Grupo nscr (transcribir)' => ['transcribir', ['trans', 'cri', 'bir']],
        ];
    }

    public static function provideCasosPrefijosFonetica(): array
    {
        return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'PF: subrayar' => ['subrayar', ['su', 'bra', 'yar'], true, 'es_ES', 'fonetica'],
            'PF: sublunar' => ['sublunar', ['su', 'blu', 'nar'], true, 'es_ES', 'fonetica'],
            'PF: deshacer' => ['deshacer', ['des', 'ha', 'cer'], true, 'es_ES', 'fonetica'],
            'PF: inhumano' => ['inhumano', ['in', 'hu', 'ma', 'no'], true, 'es_ES', 'fonetica'],
            'PF: cooperar' => ['cooperar', ['co', 'o', 'pe', 'rar'], true, 'es_ES', 'fonetica'],
            'PF: contraorden' => ['contraorden', ['con', 'tra', 'or', 'den'], true, 'es_ES', 'fonetica'],
            'PF: antiimperialista (hiato ii)' => ['antiimperialista', ['an', 'ti', 'im', 'pe', 'ria', 'lis', 'ta'], true, 'es_ES', 'fonetica'],
            'PF: rehidratar (hiato e-i por h)' => ['rehidratar', ['re', 'hi', 'dra', 'tar'], true, 'es_ES', 'fonetica'],
            'PF: desarrollar' => ['desarrollar', ['de', 'sa', 'rro', 'llar'], true, 'es_ES', 'fonetica'],
            'PF: posguerra' => ['posguerra', ['pos', 'gue', 'rra'], true, 'es_ES', 'fonetica'],
            'PF: postguerra' => ['postguerra', ['post', 'gue', 'rra'], true, 'es_ES', 'fonetica'],
            'PF: exalumno (V.xV)' => ['exalumno', ['e', 'xa', 'lum', 'no'], true, 'es_ES', 'fonetica'],
            'PF: inhábil (VC.hV)' => ['inhábil', ['in', 'há', 'bil'], true, 'es_ES', 'fonetica'],
            'PF: superhombre (VC.hV)' => ['superhombre', ['su', 'per', 'hom', 'bre'], true, 'es_ES', 'fonetica'],
            'PF: ineficaz' => ['ineficaz', ['i', 'ne', 'fi', 'caz'], true, 'es_ES', 'fonetica'],
            'PF: anormal' => ['anormal', ['a', 'nor', 'mal'], true, 'es_ES', 'fonetica'],
            'PF: improbable' => ['improbable', ['im', 'pro', 'ba', 'ble'], true, 'es_ES', 'fonetica'],
            'PF: vicepresidente' => ['vicepresidente', ['vi', 'ce', 'pre', 'si', 'den', 'te'], true, 'es_ES', 'fonetica'],
            'PF: contradecir' => ['contradecir', ['con', 'tra', 'de', 'cir'], true, 'es_ES', 'fonetica'],
            'PF: rehacer' => ['rehacer', ['re', 'ha', 'cer'], true, 'es_ES', 'fonetica'],
            'PF: excomulgar' => ['ex', 'co', 'mul', 'gar'], true, 'es_ES', 'fonetica'],
            'PF: supermercado' => ['supermercado', ['su', 'per', 'mer', 'ca', 'do'], true, 'es_ES', 'fonetica'],
            'PF: intramuscular' => ['intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'fonetica'],
            'PF: obnubilar' => ['ob', 'nu', 'bi', 'lar'], true, 'es_ES', 'fonetica'],
            'PF: adjunto' => ['adjunto', ['ad', 'jun', 'to'], true, 'es_ES', 'fonetica'],
            'PF: abjurar' => ['abjurar', ['ab', 'ju', 'rar'], true, 'es_ES', 'fonetica'],
        ];
    }

    public static function provideCasosPrefijosMorfologica(): array
    {
        return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'PM: subrayar' => ['subrayar', ['sub', 'ra', 'yar'], true, 'es_ES', 'morfologica'],
            'PM: sublunar' => ['sublunar', ['sub', 'lu', 'nar'], true, 'es_ES', 'morfologica'],
            'PM: deshacer' => ['deshacer', ['des', 'ha', 'cer'], true, 'es_ES', 'morfologica'],
            'PM: inhumano' => ['inhumano', ['in', 'hu', 'ma', 'no'], true, 'es_ES', 'morfologica'],
            'PM: cooperar' => ['cooperar', ['co', 'o', 'pe', 'rar'], true, 'es_ES', 'morfologica'], // No divide V-V
            'PM: contraorden' => ['contraorden', ['con', 'tra', 'or', 'den'], true, 'es_ES', 'morfologica'],
            'PM: antiimperialista' => ['antiimperialista', ['an', 'ti', 'im', 'pe', 'ria', 'lis', 'ta'], true, 'es_ES', 'morfologica'], // anti | ...
            'PM: rehidratar' => ['rehidratar', ['re', 'hi', 'dra', 'tar'], true, 'es_ES', 'morfologica'],
            'PM: exalumno' => ['exalumno', ['ex', 'a', 'lum', 'no'], true, 'es_ES', 'morfologica'],
            'PM: inhábil' => ['inhábil', ['in', 'há', 'bil'], true, 'es_ES', 'morfologica'],
            'PM: ineficaz' => ['ineficaz', ['in', 'e', 'fi', 'caz'], true, 'es_ES', 'morfologica'],
            'PM: anormal' => ['anormal', ['a', 'nor', 'mal'], true, 'es_ES', 'morfologica'], // 'a' es prefijo más largo que 'an' y es V+C
            'PM: improbable' => ['improbable', ['im', 'pro', 'ba', 'ble'], true, 'es_ES', 'morfologica'],
            'PM: vicepresidente' => ['vicepresidente', ['vi', 'ce', 'pre', 'si', 'den', 'te'], true, 'es_ES', 'morfologica'], // 'vice' es prefijo
            'PM: contradecir' => ['contradecir', ['con', 'tra', 'de', 'cir'], true, 'es_ES', 'morfologica'], // 'contra' es prefijo
            'PM: rehacer' => ['rehacer', ['re', 'ha', 'cer'], true, 'es_ES', 'morfologica'], // re | hacer
            'PM: excomulgar' => ['excomulgar', ['ex', 'co', 'mul', 'gar'], true, 'es_ES', 'morfologica'],
            'PM: supermercado' => ['supermercado', ['su', 'per', 'mer', 'ca', 'do'], true, 'es_ES', 'morfologica'], // super | mercado
            'PM: intramuscular' => ['intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'morfologica'], // intra | muscular
            'PM: obnubilar' => ['obnubilar', ['ob', 'nu', 'bi', 'lar'], true, 'es_ES', 'morfologica'],
            'PM: adjunto' => ['adjunto', ['ad', 'jun', 'to'], true, 'es_ES', 'morfologica'],
            'PM: abjurar' => ['abjurar', ['ab', 'ju', 'rar'], true, 'es_ES', 'morfologica'],
        ];
    }

    public static function provideCasosPrefijosAdaptativa(): array
    {
         return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'PA: subrayar (Fonética)' => ['subrayar', ['su', 'bra', 'yar'], true, 'es_ES', 'adaptativa'],
            'PA: sublunar (Morfológica)' => ['sublunar', ['sub', 'lu', 'nar'], true, 'es_ES', 'adaptativa'],
            'PA: deshacer (Fonética)' => ['deshacer', ['des', 'ha', 'cer'], true, 'es_ES', 'adaptativa'],
            'PA: inhumano (Fonética)' => ['inhumano', ['in', 'hu', 'ma', 'no'], true, 'es_ES', 'adaptativa'],
            'PA: cooperar (Fonética)' => ['cooperar', ['co', 'o', 'pe', 'rar'], true, 'es_ES', 'adaptativa'],
            'PA: contraorden (Morfológica)' => ['contraorden', ['con', 'tra', 'or', 'den'], true, 'es_ES', 'adaptativa'],
            'PA: antiimperialista (Fonética V+V)' => ['antiimperialista', ['an', 'ti', 'im', 'pe', 'ria', 'lis', 'ta'], true, 'es_ES', 'adaptativa'],
            'PA: rehidratar (Fonética V+hV)' => ['rehidratar', ['re', 'hi', 'dra', 'tar'], true, 'es_ES', 'adaptativa'],
            'PA: exalumno (Fonética X+V)' => ['exalumno', ['e', 'xa', 'lum', 'no'], true, 'es_ES', 'adaptativa'],
            'PA: inhábil (Fonética C+hV)' => ['inhábil', ['in', 'há', 'bil'], true, 'es_ES', 'adaptativa'],
            'PA: suboficial (Morfológica C+V)' => ['suboficial', ['sub', 'o', 'fi', 'cial'], true, 'es_ES', 'adaptativa'],
            'PA: ineficaz (Morfológica C+V)' => ['ineficaz', ['in', 'e', 'fi', 'caz'], true, 'es_ES', 'adaptativa'],
            'PA: anormal (Fonética V+C)' => ['anormal', ['a', 'nor', 'mal'], true, 'es_ES', 'adaptativa'],
            'PA: improbable (Fonética grupo fuerte)' => ['improbable', ['im', 'pro', 'ba', 'ble'], true, 'es_ES', 'adaptativa'],
            'PA: vicepresidente (Fonética V+C)' => ['vicepresidente', ['vi', 'ce', 'pre', 'si', 'den', 'te'], true, 'es_ES', 'adaptativa'],
            'PA: contradecir (Fonética grupo fuerte)' => ['contradecir', ['con', 'tra', 'de', 'cir'], true, 'es_ES', 'adaptativa'],
            'PA: rehacer (Fonética V+hV)' => ['rehacer', ['re', 'ha', 'cer'], true, 'es_ES', 'adaptativa'],
            'PA: excomulgar (Morfológica C+C no fuerte)' => ['excomulgar', ['ex', 'co', 'mul', 'gar'], true, 'es_ES', 'adaptativa'],
            'PA: supermercado (Fonética V+C)' => ['supermercado', ['su', 'per', 'mer', 'ca', 'do'], true, 'es_ES', 'adaptativa'],
            'PA: intramuscular (Fonética V+C)' => ['intramuscular', ['in', 'tra', 'mus', 'cu', 'lar'], true, 'es_ES', 'adaptativa'], // intra V+C
            'PA: obnubilar (Morfológica C+C no fuerte)' => ['obnubilar', ['ob', 'nu', 'bi', 'lar'], true, 'es_ES', 'adaptativa'],
            'PA: adjunto (Morfológica C+C no fuerte)' => ['adjunto', ['ad', 'jun', 'to'], true, 'es_ES', 'adaptativa'],
            'PA: abjurar (Morfológica C+C no fuerte)' => ['abjurar', ['ab', 'ju', 'rar'], true, 'es_ES', 'adaptativa'],
         ];
    }

    public static function provideCasosRegionales(): array
    {
         return [
             // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'tl ES (atleta)' => ['atleta', ['at', 'le', 'ta'], true, 'es_ES'],
            'tl MX (atleta)' => ['atleta', ['a', 'tle', 'ta'], true, 'es_MX'],
            'tl ES (atlántico)' => ['atlántico', ['at', 'lán', 'ti', 'co'], true, 'es_ES'],
            'tl MX (atlántico)' => ['atlántico', ['a', 'tlán', 'ti', 'co'], true, 'es_MX'],
            'tl ES (atlas)' => ['atlas', ['at', 'las'], true, 'es_ES'],
            'tl MX (atlas)' => ['atlas', ['a', 'tlas'], true, 'es_MX'],
         ];
    }

     public static function provideCasosLimite(): array
    {
        return [
            // Mensaje => [palabra, esperado[], hiatos?, region?, prefijos?, mensaje?]
            'Palabra Larga 1' => ['otorrinolaringologo', ['o', 'to', 'rri', 'no', 'la', 'rin', 'gó', 'lo', 'go']],
            'Palabra Larga 2 (Hiato o-e)' => ['electroencefalografista', ['e', 'lec', 'tro', 'en', 'ce', 'fa', 'lo', 'gra', 'fis', 'ta']],
            'Palabra Larga 3' => ['constitucionalidad', ['cons', 'ti', 'tu', 'cio', 'na', 'li', 'dad']],
            'Palabra Larga 4 (Hiato o-i)' => ['desoxirribonucleico', ['de', 'so', 'xi', 'rri', 'bo', 'nu', 'clei', 'co']],
            'Palabra Larga 5' => ['esternocleidomastoideo', ['es', 'ter', 'no', 'clei', 'do', 'mas', 'toi', 'de', 'o']],
            'Palabra Larga 6' => ['caleidoscopio', ['ca', 'lei', 'dos', 'co', 'pio']],
            'Palabra Larga 7' => ['anticonstitucionalmente', ['an', 'ti', 'cons', 'ti', 'tu', 'cio', 'nal', 'men', 'te']],
            'Palabra Larga 8' => ['ciclopentanoperhidrofenantreno', ['ci', 'clo', 'pen', 'ta', 'no', 'per', 'hi', 'dro', 'fe', 'nan', 'tre', 'no']],
            'Palabra Larga 9 (Hiato o-e)' => ['neuroendocrino', ['neu', 'ro', 'en', 'do', 'cri', 'no']],
            'Palabra Larga 10' => ['telecomunicaciones', ['te', 'le', 'co', 'mu', 'ni', 'ca', 'cio', 'nes']],
            'Palabra Larga 11 (Hiato i-a)' => ['fotolitografía', ['fo', 'to', 'li', 'to', 'gra', 'fí', 'a']],
            'Secuencia rara letras' => ['xzzptk', ['xzzptk']],
        ];
    }

    // -------------------------------------------------------------------------
    // Pruebas de Excepciones
    // -------------------------------------------------------------------------

    public function testPalabraVaciaLanzaExcepcion(): void
    {
        $this->expectException(InvalidWordException::class);
        $this->expectExceptionMessage('Palabra vacía.');
        $this->separador->separar('');
    }

    /**
     * @dataProvider providePalabrasInvalidas
     */
    public function testCaracteresInvalidosLanzaExcepcion(string $palabraInvalida): void
    {
        $this->expectException(InvalidWordException::class);
        $this->expectExceptionMessageMatches("/Caracteres inválidos en '.*'/"); // Mensaje genérico
        $this->separador->separar($palabraInvalida);
    }

    public static function providePalabrasInvalidas(): array
    {
        return [
            'Con guion' => ['test-word'],
            'Con número' => ['año1'],
            'No latino' => ['你好'],
            'Con espacio' => ['hola mundo'],
            'Con símbolo' => ['palabra$'],
        ];
    }

    // -------------------------------------------------------------------------
    // Prueba de Caché
    // -------------------------------------------------------------------------

    public function testCacheFuncionaCorrectamente(): void
    {
        $palabra = 'constitucional';
        $params = [true, 'es_ES', 'fonetica'];

        // 1. Primera llamada (calculado)
        $resultado1 = $this->separador->separar($palabra, ...$params);
        $this->assertEquals('calculado', $resultado1['fuente']);
        $this->assertEquals(1, SeparadorSilabasRAE::getCacheSize());

        // 2. Segunda llamada igual (cache)
        $resultado2 = $this->separador->separar($palabra, ...$params);
        $this->assertEquals('cache', $resultado2['fuente']);
        $this->assertEquals($resultado1['silabas'], $resultado2['silabas']);
        $this->assertEquals(1, SeparadorSilabasRAE::getCacheSize()); // Tamaño no debe cambiar

        // 3. Llamada con región diferente (calculado)
        $resultado3 = $this->separador->separar($palabra, true, 'es_MX', 'fonetica');
        $this->assertEquals('calculado', $resultado3['fuente']);
        $this->assertEquals(2, SeparadorSilabasRAE::getCacheSize());

        // 4. Llamada con prefijo diferente (calculado)
        $resultado4 = $this->separador->separar($palabra, true, 'es_ES', 'morfologica');
        $this->assertEquals('calculado', $resultado4['fuente']);
        $this->assertEquals(3, SeparadorSilabasRAE::getCacheSize());

        // 5. Limpiar caché
        SeparadorSilabasRAE::limpiarCache();
        $this->assertEquals(0, SeparadorSilabasRAE::getCacheSize());
    }

    // -------------------------------------------------------------------------
    // Prueba de HTML (Simple)
    // -------------------------------------------------------------------------
    public function testGenerarHtmlSimple(): void
    {
        $silabas = ['ca', 'sa'];
        $htmlEsperado = '<span class="silaba">ca</span><span class="silaba">sa</span>';
        $this->assertEquals($htmlEsperado, $this->separador->generarHtml($silabas));

        $silabasConTilde = ['in', 'há', 'bil'];
        $htmlEsperadoTilde = '<span class="silaba">in</span><span class="silaba">há</span><span class="silaba">bil</span>';
        $this->assertEquals($htmlEsperadoTilde, $this->separador->generarHtml($silabasConTilde));

        $this->assertEquals('', $this->separador->generarHtml([])); // Array vacío
    }
}

// =============================================================================
// --- EJECUCIÓN (Ejecutar con PHPUnit) ---
// =============================================================================
// echo "\n(Este script debe ejecutarse con PHPUnit)";
// Ejemplo de uso directo (si no se usa PHPUnit)
// $sep = new Kuasarx\Linguistica\SeparadorSilabasRAE();
// print_r($sep->separar('constitucional'));

?>
