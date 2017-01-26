<?php
if (!defined('_GNUBOARD_')) exit;

// 게시글보기 썸네일생성과 EXIF 정보
function get_view_thumbnail_exif($contents, $view=false, $thumb_width=0)
{
    global $board, $config;

    if (!$thumb_width)
        $thumb_width = $board['bo_image_width'];

    // $contents 중 img 태그 추출
    $matchs = get_editor_image($contents, $view);

    if(empty($matchs))
        return $contents;

    for($i=0; $i<count($matchs[1]); $i++) {
        // 이미지 path 구함
        $p = parse_url($matchs[1][$i]);

        if(strpos($p['path'], "/data/") != 0)
            $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $data_path = $p['path'];

        $srcfile = G5_PATH.$data_path;
        $exif_info = '';

        if(is_file($srcfile)) {
            // 썸네일 높이
            $size = @getimagesize($srcfile);
            if(empty($size))
                continue;

            // exif 정보
            if($size[2] == 2) {
                $exif = get_exif_info($srcfile);

                if(!empty($exif)) {
                    $exif_info = print_exif_info($exif);
                }
            }

            // Animated GIF 체크
            $is_animated = false;
            if($size[2] == 1) {
                $is_animated = is_animated_gif($srcfile);
            }

            $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
            $filename = basename($srcfile);
            $filepath = dirname($srcfile);

            // 원본 width가 thumb_width보다 작다면 썸네일 생성
            if(!$is_animated && $size[0] > $thumb_width)
                $thumb_file = thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, false);
            else
                $thumb_file = $filename;

            $img_tag = $matchs[0][$i];
            preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $img_tag, $malt);
            $alt = get_text($malt[1]);
            $thumb_tag = '<img src="'.G5_URL.str_replace($filename, $thumb_file, $data_path).'" alt="'.$alt.'"/>';

            // $img_tag에 editor 경로가 있으면 원본보기 링크 추가
            if(strpos($matchs[1][$i], 'data/editor') && preg_match("/\.({$config['cf_image_extension']})$/i", $filename)) {
                $imgurl = str_replace(G5_URL, "", $matchs[1][$i]);
                $thumb_tag = '<a href="'.G5_BBS_URL.'/view_image.php?fn='.urlencode($imgurl).'" target="_blank" class="view_image">'.$thumb_tag.'</a>';

                if($exif_info)
                    $thumb_tag .= $exif_info;
            }

            $contents = str_replace($img_tag, $thumb_tag, $contents);

            if(strpos($matchs[1][$i], 'data/file') && preg_match("/\.({$config['cf_image_extension']})$/i", $filename)){
                if($exif_info) {
                    $contents .= $exif_info;
                }
            }
        }
    }

    return $contents;
}

// EXIF 정보 출력
function print_exif_info($exif)
{
    $sep = false;
    $sp = '&nbsp;&nbsp;&nbsp;&nbsp;';
    $str = '<p class="exif_info">';

    if(isset($exif['Model'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['Model'];
        $sep = true;
    }

    if(isset($exif['Mode'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['Mode'];
        $sep = true;
    }

    if(isset($exif['MeteringModel'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['MeteringMode'];
        $sep = true;
    }

    if(isset($exif['ShutterSpeed'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['ShutterSpeed'];
        $sep = true;
    }

    if(isset($exif['FNumber'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['FNumber'];
        $sep = true;
    }

    if(isset($exif['ExposureBias'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['ExposureBias'];
        $sep = true;
    }

    if(isset($exif['FocalLength'])) {
        if($sep)
            $str .= $sp;
        $str .= $exif['FocalLength'];
        $sep = true;
    }

    if(isset($exif['ISO'])) {
        if($sep)
            $str .= $sp;
        $str .= 'ISO-'.$exif['ISO'];
        $sep = true;
    }

    if(isset($exif['Datetime'])) {
        if($sep)
            $str .= $sp;
        $tmp = explode(' ', $exif['Datetime']);
        $str .= str_replace(':', '-', $tmp[0]).' '.$tmp[1];
        $sep = true;
    }

    $str .= '</p>';
    return $str;
}

// EXIF 정보를 배열로 리턴
function get_exif_info($file)
{
    if(!is_file($file))
        return false;

    // EXIF Data
    $exif = exif_read_data($file, 'EXIF', 0);

    if($exif === false)
        return false;

    // 제조사
    if(array_key_exists('Make', $exif))
        $result['Maker'] = $exif['Make'];

    // 모델
    if(array_key_exists('Model', $exif))
        $result['Model'] = $exif['Model'];

    // 조리개값
    if(array_key_exists('ApertureFNumber', $exif['COMPUTED']))
        $result['FNumber'] = strtolower($exif['COMPUTED']['ApertureFNumber']);

    // 셔터스피드
    if(array_key_exists('ExposureTime', $exif)) {
        $t = explode("/", $exif['ExposureTime']);
        $t1 = (int)$t[0];
        $t2 = (int)$t[1];

        if($t1 >= $t2) {
            $exp = $t1 / $t2;
        } else {
            $exp = $t1 / $t1 .'/'. floor($t2 / $t1);
        }

        $result['ShutterSpeed'] = $exp.'sec';
    }

    // 촬영모드
    if(array_key_exists('ExposureProgram', $exif)) {
        switch($exif['ExposureProgram']) {
            case 0:
                $mode = 'Auto Mode';
                break;
            case 1:
                $mode = 'Manual';
                break;
            case 2:
                $mode = 'Auto Mode';
                break;
            case 3:
                $mode = 'Aperture Priority';
                break;
            case 4:
                $mode = 'Shutter Priority';
                break;
        }

        $result['Mode'] = $mode;
    }

    // 촬영일시
    if(array_key_exists('DateTimeOriginal', $exif))
        $result['Datetime'] = $exif['DateTimeOriginal'];

    // ISO
    if(array_key_exists('ISOSpeedRatings', $exif)) {
        if(is_array($exif['ISOSpeedRatings']))
            $result['ISO'] = $exif['ISOSpeedRatings'][0];
        else
            $result['ISO'] = $exif['ISOSpeedRatings'];
    }

    // 초점거리
    if(array_key_exists('FocalLength', $exif)) {
        $t = explode("/", $exif['FocalLength']);
        $result['FocalLength'] = round(((int)$t[0] / (int)$t[1]), 1).'mm';
    } else if(array_key_exists('FocalLengthIn35mmFilm', $exif)) {
        $t = explode("/", $exif['FocalLengthIn35mmFilm']);
        $result['FocalLength'] = (int)$t[0] / (int)$t[1].'mm';
    }

    // 노출보정
    if(array_key_exists('ExposureBiasValue', $exif)) {
        $t = explode("/", $exif['ExposureBiasValue']);
        $bias = round(((int)$t[0] / (int)$t[1]), 2);

        $result['ExposureBias'] = $bias.'EV';
    }

    // 측광
    if(array_key_exists('MeteringMode', $exif)) {
        switch($exif['MeteringMode']) {
            case 1:
                $mode = 'Average';
                break;
            case 2:
                $mode = 'Center Weighted Average';
                break;
            case 3:
                $mode = 'Spot';
                break;
            case 5:
                $mode = 'Multi Segment';
                break;
            case 6:
                $mode = 'Partial';
                break;
            default:
                $mode = 'Unknown';
                break;
        }

        $result['MeteringMode'] = $mode;
    }

    // 화이트밸런스
    if(array_key_exists('WhiteBalance', $exif)) {
        switch($exif['WhiteBalance']) {
            case 1:
                $mode = 'Manual';
                break;
            default:
                $mode = 'Auto';
                break;
        }

        $result['WhiteBalance'] = $mode;
    }

    // Flash
    if(array_key_exists('Flash', $exif)) {
        switch($exif['Flash']) {
            case 7:
                $mode = 'On';
                break;
            case 9:
                $mode = 'On Compulsory';
                break;
            case 16:
                $mode = 'Off Compulsory';
                break;
            case 73:
                $mode = 'On Compulsory Red-eye reduction';
                break;
            default:
                $mode = 'Unknown';
                break;
        }

        $result['Flash'] = $mode;
    }

    return $result;
}
?>