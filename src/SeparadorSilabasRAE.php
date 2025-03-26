<?php

declare(strict_types=1);

namespace Kuasarx\Linguistica;

/**
 * Excepción personalizada para palabras inválidas durante la separación silábica.
 */
class InvalidWordException extends \InvalidArgumentException {}

/**
 * Clase SeparadorSilabasRAE v1.4.2
 *
 * Implementa las reglas de división silábica del español según la RAE,
 * incluyendo hiatos, diptongos, triptongos, grupos consonánticos, dígrafos,
 * h intercalada, variantes regionales (tl) y estrategias de prefijos.
 * Incluye caché LRU y rastreo opcional.
 *
 * @see RAE Ortografía (2010), Cap IV, § 4.1.1.1.1.2
 * Developer: Juan Camacho
 * Email: kuasarx@gmail.com
 * @version    1.4.2 - Corregida lógica de hiato/diptongo para D+Da y Da+D (ej: veintiún).
 */
class SeparadorSilabasRAE
{
    // --- Constantes (sin cambios) ---
    private const VOCALES_FUERTES_NORM = ['a', 'e', 'o'];
    private const VOCALES_DEBILES_NORM = ['i', 'u', 'ü'];
    private const VOCALES_NORM = ['a', 'e', 'o', 'i', 'u', 'ü'];
    private const VOCALES_FUERTES_ACENTUADAS = ['á', 'é', 'ó'];
    private const VOCALES_DEBILES_ACENTUADAS = ['í', 'ú'];
    private const VOCALES_CON_TILDE = ['á', 'é', 'í', 'ó', 'ú', 'ü'];
    private const TODAS_VOCALES_ORIG = ['a', 'e', 'i', 'o', 'u', 'á', 'é', 'í', 'ó', 'ú', 'ü'];
    private const CONSONANTES = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'ñ', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];
    private const DIGRAFOS = ['ch', 'll', 'rr'];
    private const GRUPOS_INSEPARABLES = ['pl', 'bl', 'cl', 'gl', 'fl', 'pr', 'br', 'tr', 'dr', 'cr', 'gr', 'fr'];
    private const GRUPOS_INSEPARABLES_FUERTES = ['pr', 'br', 'tr', 'dr', 'cr', 'gr', 'fr'];
    private const PREFIJOS = [ 'a', 'ab', 'abs', 'ante', 'anti', 'bi', 'bis', 'biz', 'co', 'con', 'contra', 'de', 'des', 'di', 'dis', 'e', 'en', 'entre', 'ex', 'extra', 'hiper', 'hipo', 'i', 'im', 'in', 'inter', 'intra', 'o', 'ob', 'obs', 'omn', 'per', 'pos', 'post', 'pre', 'pro', 're', 'res', 'retro', 'sin', 'so', 'sobre', 'sub', 'subs', 'super', 'supra', 'trans', 'tras', 'tri', 'ultra', 'un', 'uni', 'vi', 'vice', 'viz' ];
    private const MAX_CACHE_SIZE = 1000;
    private static array $cache = [];
    private static array $cacheOrder = [];

    // --- Propiedades (sin cambios) ---
    private string $palabraOriginal = '';
    private string $palabraNormalizada = '';
    private string $palabraMinusculas = '';
    private int $longitud = 0;
    private bool $incluirHiatos = true;
    private string $regionalismo = 'es_ES';
    private string $estrategiaPrefijos = 'fonetica';
    private array $silabas = [];
    private array $puntosDivision = [];
    private array $excepcionesAplicadas = [];
    private bool $enableTracing = false;
    private array $traceLog = [];

    /** Habilita/Deshabilita el rastreo. */
    public function enableTracing(bool $enable = true): void { $this->enableTracing = $enable; }

    /** Separa la palabra en sílabas. */
    public function separar( string $palabra, bool $incluirHiatos = true, string $regionalismo = 'es_ES', string $estrategiaPrefijos = 'fonetica' ): array {
        $startTime = microtime(true); $this->traceLog = []; $this->trace("--- Inicio separar('{$palabra}', h={$incluirHiatos}, r={$regionalismo}, p={$estrategiaPrefijos}) ---");
        $this->palabraOriginal = trim($palabra); $this->incluirHiatos = $incluirHiatos; $this->regionalismo = $regionalismo; $this->estrategiaPrefijos = in_array($estrategiaPrefijos, ['fonetica', 'morfologica', 'adaptativa']) ? $estrategiaPrefijos : 'fonetica';
        try { $this->validarPalabra($this->palabraOriginal); } catch (InvalidWordException $e) { $this->trace("Error Validación: ".$e->getMessage()); throw $e; }
        $cacheKey = $this->palabraOriginal.'_'.($incluirHiatos?'h1':'h0').'_'.$regionalismo.'_'.$this->estrategiaPrefijos; $this->trace("Cache key: {$cacheKey}");
        if (isset(self::$cache[$cacheKey])) { $this->trace("Cache HIT."); if (($i=array_search($cacheKey,self::$cacheOrder))!==false)unset(self::$cacheOrder[$i]); self::$cacheOrder[]=$cacheKey; $res=self::$cache[$cacheKey]; $res['tiempo_ms']=(microtime(true)-$startTime)*1000; $res['fuente']='cache'; if($this->enableTracing){$this->trace("--- Fin (caché) ---");$res['trace']=$this->traceLog;} return $res; }
        $this->trace("Cache MISS. Calculando..."); $this->inicializarProceso(); $tTotal=0.0; if($this->longitud>0){ $this->ejecutarSeparacion(); $tTotal=(microtime(true)-$startTime)*1000; $this->trace("Cálculo en {$tTotal} ms"); $this->trace("Res: Sílabas=[".implode('-',$this->silabas)."], Puntos=[".implode(',',$this->puntosDivision)."]"); } else{$this->trace(" Palabra vacía.",1);} $this->trace("--- Fin (calculado) ---");
        $res=['silabas'=>$this->silabas,'puntos_division'=>$this->puntosDivision,'excepciones_aplicadas'=>array_values(array_unique($this->excepcionesAplicadas)),'tiempo_ms'=>$tTotal,'fuente'=>'calculado',]; if($this->enableTracing)$res['trace']=$this->traceLog; $this->actualizarCache($cacheKey,$res); return $res;
    }
    /** Genera HTML. */
    public function generarHtml(array $silabas): string { if(empty($silabas))return''; $esc=array_map(fn($s)=>htmlspecialchars($s,ENT_QUOTES,'UTF-8'),$silabas); return'<span class="silaba">'.implode('</span><span class="silaba">',$esc).'</span>'; }
    private function trace(string $message, int $level = 0): void { if ($this->enableTracing) $this->traceLog[] = str_repeat('  ', $level).$message; }
    private function validarPalabra(string $palabra): void { if($palabra==='')throw new InvalidWordException("Palabra vacía."); if(!preg_match('/^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ]+$/u',$palabra))throw new InvalidWordException("Caracteres inválidos en '{$palabra}'."); }
    private function inicializarProceso(): void { $this->palabraMinusculas = mb_strtolower($this->palabraOriginal, 'UTF-8'); $this->palabraNormalizada = $this->normalizarTexto($this->palabraMinusculas); $this->longitud = mb_strlen($this->palabraOriginal, 'UTF-8'); $this->silabas = []; $this->puntosDivision = []; $this->excepcionesAplicadas = []; $this->trace("   -> init(): palabra='{$this->palabraOriginal}', len={$this->longitud}", 1); }
    private function normalizarTexto(string $texto): string { static $b=['á','é','í','ó','ú']; static $r=['a','e','i','o','u']; return str_replace($b, $r, $texto); }
    private function ejecutarSeparacion(): void { /* Sin cambios */ $this->trace("-> ejecutarSeparacion()"); $idxActual=0; $inicioSilaba=0; $ptoPrefijo=-1; if($this->estrategiaPrefijos!=='fonetica'){ $this->trace(" Eval prefijos ({$this->estrategiaPrefijos})",1); $ptoPrefijo=$this->buscarPuntoDivisionPrefijo(); if($ptoPrefijo>0&&$ptoPrefijo<$this->longitud){ $prefStr=mb_substr($this->palabraOriginal,0,$ptoPrefijo,'UTF-8'); $this->trace(" Prefijo pot: '{$prefStr}', pto: {$ptoPrefijo}",1); $applyMorfo=($this->estrategiaPrefijos==='morfologica')||($this->estrategiaPrefijos==='adaptativa'&&$this->esSeparacionMorfologicaPreferible($ptoPrefijo)); if($applyMorfo){ $this->trace(" Aplicando div morfo.",2); if($this->estrategiaPrefijos==='morfologica')$this->excepcionesAplicadas[]="Estrategia_Morfologica_Activa"; else $this->excepcionesAplicadas[]="Estrategia_Adaptativa_Prefiere_Morfologica"; $sepPref=new self(); $resPref=$sepPref->separar($prefStr,$this->incluirHiatos,$this->regionalismo,'fonetica'); $this->trace(" Sílabas pref: [".implode('-',$resPref['silabas'])."]",2); $this->silabas=array_merge($this->silabas,$resPref['silabas']); $this->puntosDivision=array_merge($this->puntosDivision,$resPref['puntos_division']); $this->excepcionesAplicadas=array_merge($this->excepcionesAplicadas,array_diff($resPref['excepciones_aplicadas'],$this->excepcionesAplicadas)); $this->excepcionesAplicadas[]="Division_Morfologica_Prefijo: ".$prefStr; if(!in_array($ptoPrefijo,$this->puntosDivision))$this->puntosDivision[]=$ptoPrefijo; sort($this->puntosDivision); $this->trace(" Ptos div: [".implode(',',$this->puntosDivision)."]",2); $inicioSilaba=$ptoPrefijo; $idxActual=$ptoPrefijo; $this->trace(" Continuando fonética desde {$inicioSilaba}",2); } else { $this->trace(" Prefijo ignorado.",1); $this->excepcionesAplicadas[]="Prefijo_Detectado_Ignorado: ".$prefStr; } } else { $this->trace(" No prefijo.",1); } } else { $this->trace(" Estrategia fonética.",1); } $this->trace(" Iniciando bucle fonético desde {$inicioSilaba}",1); while($idxActual<$this->longitud){ $this->trace(" --- Iter: inicio={$inicioSilaba} ('".mb_substr($this->palabraOriginal,$inicioSilaba)."') ---",2); $lenSilaba=$this->encontrarLongitudSiguienteSilaba($inicioSilaba,$idxActual); if($lenSilaba<=0){$this->trace(" Error: len<=0 ({$lenSilaba}). Terminando.",3); $this->excepcionesAplicadas[]="Error_Calculo_Longitud_Silaba_Indice_".$inicioSilaba; break;} $silaba=mb_substr($this->palabraOriginal,$inicioSilaba,$lenSilaba,'UTF-8'); $this->trace(" Sílaba: '{$silaba}' (len {$lenSilaba})",3); $this->silabas[]=$silaba; $inicioSilaba+=$lenSilaba; $idxActual=$inicioSilaba; if($inicioSilaba<$this->longitud&&$inicioSilaba!==$ptoPrefijo){ if(!in_array($inicioSilaba,$this->puntosDivision)){$this->trace(" Pto div: {$inicioSilaba}",3);$this->puntosDivision[]=$inicioSilaba;}else{$this->trace(" Pto div {$inicioSilaba} ya existe.",3);}}} sort($this->puntosDivision); $this->trace("<- fin ejecutarSeparacion()"); }
    private function encontrarLongitudSiguienteSilaba(int $inicioSilaba, int $idxAnalisis): int { /* Sin cambios */ $this->trace("-> findLen(inicio={$inicioSilaba})",1); if($inicioSilaba>=$this->longitud){$this->trace(" Fin palabra.",2);return 0;} if($inicioSilaba===$this->longitud-1){$this->trace(" Último char.",2);return 1;} $i=$inicioSilaba; while($i<$this->longitud&&!$this->esVocal($this->charAt($this->palabraMinusculas,$i)))$i++; if($i===$this->longitud){$this->trace(" No vocales.",2);return $this->longitud-$inicioSilaba;} $this->trace(" Vocal en {$i}: '".$this->charAt($this->palabraMinusculas,$i)."'",2); $nucleo=$this->analizarNucleoVocalico($i); $finN=$nucleo['fin']; $tipoN=$nucleo['tipo']; $ptoH=$nucleo['punto_hiato']; $this->trace(" Núcleo: tipo={$tipoN}, fin={$finN}, ptoH={$ptoH}",2); if($tipoN==='hiato'&&$this->incluirHiatos&&$ptoH>$inicioSilaba){$len=$ptoH-$inicioSilaba; $this->trace(" Hiato. Cortando en {$ptoH}.",3); $this->excepcionesAplicadas[]="Hiato_Separado"; $this->trace("<- fin(hiato), len={$len}",1); return $len;} $idxPN=$finN+1; if($idxPN>=$this->longitud){$len=$this->longitud-$inicioSilaba; $this->trace(" Núcleo al final.",2); $this->trace("<- fin(fin palabra), len={$len}",1); return $len;} $this->trace(" Buscando C desde {$idxPN}",2); $cons=[]; $idxCons=[]; $j=$idxPN; while($j<$this->longitud&&!$this->esVocal($this->charAt($this->palabraMinusculas,$j))){$c1=$this->charAt($this->palabraMinusculas,$j); $lenC=1; $pDig=''; if($j+1<$this->longitud){$c2=$this->charAt($this->palabraMinusculas,$j+1); $pDig=$c1.$c2; if(in_array($pDig,self::DIGRAFOS))$lenC=2; elseif(($c1=='q'&&$c2=='u')||($c1=='g'&&$c2=='u'&&$j+2<$this->longitud&&$this->esVocalEI($this->charAt($this->palabraMinusculas,$j+2))))$lenC=2;} if($lenC===1){$cons[]=$c1;$idxCons[]=$j;}else{$cons[]=$pDig;$idxCons[]=$j;}$j+=$lenC;} $numC=count($cons); $idxTC=$j; $this->trace(" Encontradas {$numC} C: [".implode(',',$cons)."]. Tras C: {$idxTC}",2); $lenCalc=1; switch($numC){ case 0: $lenCalc=$finN-$inicioSilaba+1; break; case 1: $idxC=$idxCons[0]; $fin=($idxTC>=$this->longitud); if($fin){$lenCalc=$this->longitud-$inicioSilaba;}else{$lenCalc=$idxC-$inicioSilaba;} break; case 2: $c1i=$idxCons[0];$c2i=$idxCons[1];$c1n=$this->charAt($this->palabraNormalizada,$c1i);$c2n=$this->charAt($this->palabraNormalizada,$c2i);$g=$c1n.$c2n; if(in_array($g,self::GRUPOS_INSEPARABLES)||strlen($cons[0])>1){$ex=(strlen($cons[0])>1)?"D/QUGU:".$cons[0]:"Grupo_Insep:".$g;$lenCalc=$c1i-$inicioSilaba;$this->excepcionesAplicadas[]=$ex;}elseif($g==='tl'){if($this->regionalismo==='es_MX'){$ex="tl_MX: tl";$lenCalc=$c1i-$inicioSilaba;}else{$ex="tl_ES: tl";$lenCalc=$c2i-$inicioSilaba;}$this->excepcionesAplicadas[]=$ex;}else{$lenCalc=$c2i-$inicioSilaba;} break; case 3: $c2i=$idxCons[1];$c3i=$idxCons[2];$g23=$this->charAt($this->palabraNormalizada,$c2i).$this->charAt($this->palabraNormalizada,$c3i); if(in_array($g23,self::GRUPOS_INSEPARABLES)){$ex="CCC->VC.CCV: ".$g23;$lenCalc=$c2i-$inicioSilaba;}else{$ex="CCC->VCC.CV";$lenCalc=$c3i-$inicioSilaba;}$this->excepcionesAplicadas[]=$ex; break; case 4: $c3i=$idxCons[2];$this->excepcionesAplicadas[]="Grupo_CCCC_VCC.CCV";$lenCalc=$c3i-$inicioSilaba; break; default: $c3i=$idxCons[2];$this->excepcionesAplicadas[]="Grupo_C_Largo_VCC.RestoV";$lenCalc=$c3i-$inicioSilaba; break; } if($lenCalc<=0&&$inicioSilaba<$this->longitud){$this->trace(" WARN: len<=0 ({$lenCalc}). Forzando 1.",2);$lenCalc=1;} $this->trace("<- fin, len final {$lenCalc}", 1); return $lenCalc; }

    /** Analiza núcleo vocálico (v1.4.2 - Hiato D+Da/Da+D corregido). */
    private function analizarNucleoVocalico(int $inicio): array
    {
        $this->trace("-> analizarNucleoVocalico(inicio={$inicio})", 2);
        $i=$inicio; $indices=[]; $tipos=[]; $hPrevia=-1; $ptoHiato=-1; $dbg="";
        while($i<$this->longitud){ $cO=$this->charAt($this->palabraMinusculas,$i); $cN=$this->charAt($this->palabraNormalizada,$i); $isV=$this->esVocal($cO); $isH=$cN==='h'&&count($indices)>0&&$i+1<$this->longitud&&$this->esVocal($this->charAt($this->palabraMinusculas,$i+1)); $isY=$cN==='y'&&($i===$this->longitud-1||($i+1<$this->longitud&&!$this->esVocal($this->charAt($this->palabraMinusculas,$i+1))));
            if($isV){$indices[]=$i; $t=($this->esVocalFuerte($cO)?'f':($this->esVocalDebilAcentuada($cO)?'da':'d')); $tipos[]=$t; $dbg.="{$cO}({$t})"; $hPrevia=-1;}elseif($isH){$indices[]=$i;$tipos[]='h';$hPrevia=$i;$dbg.="{$cO}(h)";}elseif($isY){$indices[]=$i;$tipos[]='d';$this->excepcionesAplicadas[]="Y_Final_Como_Vocal";$dbg.="{$cO}(y->d)";$i++;break;}else break; $i++;
        }
        if(empty($indices)){$this->trace("<- fin (error: sin índices)",2);return['fin'=>$inicio-1,'tipo'=>'error','punto_hiato'=>-1];}
        $finSeq=end($indices); $tiposV=[]; $idxV=[]; for($k=0;$k<count($indices);$k++)if($tipos[$k]!=='h'){$tiposV[]=$tipos[$k];$idxV[]=$indices[$k];} $nV=count($tiposV);
        $this->trace("   Secuencia: '{$dbg}', Vocales={$nV}, Fin={$finSeq}", 3);

        // Buscar PRIMER hiato
        for ($k=0; $k < $nV - 1; $k++) {
            $iV1=$idxV[$k]; $tV1=$tiposV[$k]; $iV2=$idxV[$k+1]; $tV2=$tiposV[$k+1];
            $v1N=$this->charAt($this->palabraNormalizada,$iV1); $v2N=$this->charAt($this->palabraNormalizada,$iV2);

            // --- REGLAS DE HIATO (v1.4.2) ---
            $esH = false;
            if ($tV1=='f' && $tV2=='f') { $esH=true; $this->trace(" Hiato FF",4); } // F + F
            elseif ($tV1=='f' && $tV2=='da') { $esH=true; $this->trace(" Hiato F+Da",4); } // F + Da
            elseif ($tV1=='da' && $tV2=='f') { $esH=true; $this->trace(" Hiato Da+F",4); } // Da + F
            elseif ($tV1=='da' && $tV2=='da') { $esH=true; $this->trace(" Hiato Da+Da",4); } // Da + Da
            elseif ($v1N === $v2N) { $esH=true; $this->trace(" Hiato VV idénticas",4); } // Vocales Idénticas (aa, ee, ii, oo, uu)
            // Combinaciones D+Da y Da+D NO son hiato.

            if($esH){
                $this->trace("      Hiato confirmado: {$tV1}({$iV1}) vs {$tV2}({$iV2})",4);
                $hIdx=-1; for($h=$iV1+1;$h<$iV2;$h++)if($this->charAt($this->palabraMinusculas,$h)==='h'){$hIdx=$h;break;}
                if($hIdx!==-1){$ptoHiato=$hIdx;$this->excepcionesAplicadas[]="H_Intercalada_Hiato"; $this->trace(" Corte ANTES H:{$ptoHiato}",5);}
                else{$ptoHiato=$iV2; $this->trace(" Corte ANTES V2:{$ptoHiato}",5);}
                break; // Encontrado primer hiato
            }
        }

        $tipoFin='error'; $ptoFin=-1;
        if($ptoHiato!==-1){$tipoFin='hiato';$ptoFin=$ptoHiato;}
        elseif($nV===3&&$tiposV[0]==='d'&&$tiposV[1]==='f'&&$tiposV[2]==='d'){$tipoFin='triptongo';} // DFD
        elseif($nV>=2){$tipoFin='diptongo';} // VV o VVV sin hiato inicial
        elseif($nV===1){$tipoFin='vocal';}

        $this->trace("<- fin analizarNucleoVocalico, tipo={$tipoFin}, ptoHiato={$ptoFin}", 2);
        return['fin'=>$finSeq,'tipo'=>$tipoFin,'punto_hiato'=>$ptoFin];
    }

    /** Busca prefijo aplicable. */
    private function buscarPuntoDivisionPrefijo(): int { $prefOrd=self::PREFIJOS; usort($prefOrd, fn($a,$b)=>mb_strlen($b,'UTF-8')-mb_strlen($a,'UTF-8')); foreach($prefOrd as $p){ $len=mb_strlen($p,'UTF-8'); if($len>0&&$len<$this->longitud&&mb_substr($this->palabraMinusculas,0,$len,'UTF-8')===$p){ $u=mb_substr($p,-1,1,'UTF-8'); $pr=$this->charAt($this->palabraMinusculas,$len); if($this->esVocal($u)&&$this->esVocal($pr))continue; return $len; } } return -1; }

    /** Heurística adaptativa (con corrección x+V). */
    private function esSeparacionMorfologicaPreferible(int $idxPref): bool
    {
        $this->trace("-> esSeparacionMorfologicaPreferible({$idxPref})", 3);
        $pref=mb_substr($this->palabraMinusculas,0,$idxPref,'UTF-8'); $u=mb_substr($pref,-1,1,'UTF-8'); $p=$this->charAt($this->palabraMinusculas,$idxPref);
        $this->trace("   Pref='{$pref}', U='{$u}', P='{$p}'", 4); $prefFon=true;

        if ($u === 'x' && $this->esVocal($p)) { $this->trace("      X+V: Fonética", 5); $this->excepcionesAplicadas[]="Adaptativa_Ignora_Morfologica_X_V"; $prefFon=true; }
        elseif ($this->esConsonante($u) && $p === 'h' && $idxPref + 1 < $this->longitud && $this->esVocal($this->charAt($this->palabraMinusculas, $idxPref + 1))) { $this->trace("      C+hV: Fonética", 5); $this->excepcionesAplicadas[]="Adaptativa_Ignora_Morfologica_CHV"; $prefFon=true; }
        elseif ($this->esConsonante($u) && $this->esVocal($p)) { $this->trace("      C+V: Morfológica", 5); $this->excepcionesAplicadas[]="Adaptativa_Prefiere_Morfologica_CV"; $prefFon=false; }
        elseif ($this->esConsonante($u) && $this->esConsonante($p)) {
             $this->trace("      C+C", 5); $g=$u.$p; $gn=str_replace('h','',$g); $this->trace("         Grupo='{$g}', Norm='{$gn}'", 6);
             if (in_array($gn, self::GRUPOS_INSEPARABLES_FUERTES)) { $this->trace("         Fuerte: Fonética", 7); $this->excepcionesAplicadas[]="Adaptativa_Ignora_Morfologica_Grupo_Fuerte"; $prefFon=true; }
             elseif (in_array($gn, self::GRUPOS_INSEPARABLES) || !in_array($gn, self::DIGRAFOS)) { $this->trace("         Débil/Otro: Morfológica", 7); $this->excepcionesAplicadas[]="Adaptativa_Prefiere_Morfologica_CC_No_Fuerte"; $prefFon=false; }
             else { $this->trace("         Dígrafo: Fonética", 7); $this->excepcionesAplicadas[]="Adaptativa_Ignora_Morfologica_Digrafo"; $prefFon=true; }
        } else { $this->trace("   <- Default: Fonética", 4); $prefFon=true; }

        $this->trace("   <- Decisión: ".($prefFon?'Fonética':'Morfológica'), 4);
        return !$prefFon;
    }

    // --- Helpers ---
    private function charAt(string $str, int $index): string { if ($index < 0 || $index >= mb_strlen($str, 'UTF-8')) return ''; return mb_substr($str, $index, 1, 'UTF-8'); }
    private function esVocal(string $char): bool { return $char !== '' && in_array($char, self::TODAS_VOCALES_ORIG); }
    private function esVocalFuerte(string $char): bool { $norm = $this->normalizarTexto($char); return $norm !== '' && in_array($norm, self::VOCALES_FUERTES_NORM); }
    private function esVocalDebil(string $char): bool { $norm = $this->normalizarTexto($char); return $norm !== '' && in_array($norm, self::VOCALES_DEBILES_NORM); }
    private function esVocalDebilAcentuada(string $char): bool { return $char !== '' && in_array($char, self::VOCALES_DEBILES_ACENTUADAS); }
    private function esVocalEI(string $char): bool { $norm = $this->normalizarTexto($char); return $norm === 'e' || $norm === 'i'; }
    private function esConsonante(string $char): bool { return $char !== '' && mb_strlen($char, 'UTF-8') === 1 && preg_match('/^\p{L}$/u', $char) && !$this->esVocal($char); }
    private function actualizarCache(string $key, array $value): void { unset($value['trace']); if (count(self::$cache) >= self::MAX_CACHE_SIZE) { $lruKey = array_shift(self::$cacheOrder); if ($lruKey !== null) unset(self::$cache[$lruKey]); } self::$cache[$key] = $value; self::$cacheOrder[] = $key; }
    public static function limpiarCache(): void { self::$cache = []; self::$cacheOrder = []; }
    public static function getCacheSize(): int { return count(self::$cache); }

} // Fin clase
