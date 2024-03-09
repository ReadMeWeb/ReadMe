<?php
class Validator{

    // constanti estensioni valide per immagini definite nell'estensione php exif
    const VALID_IMGS_EXT = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

    static private function create_msg_error(string $body): string{
        return '<p role="alert" class="input-error">' . $body . '</p>';
    }

    static function check_input_dim(string $val, string $val_name, int $min_len=0, int $max_len=PHP_INT_MAX): string {

        $msg = '';
        if(strlen($val) > $max_len) {
            $errore = "Il valore {$val_name} deve avere meno di {$max_len} caratteri.";
            $msg = self::create_msg_error($errore);
        }
        else if(strlen($val <= $min_len)) {
            $errore = "Il valore {$val_name} deve avere più di {$min_len} caratteri diversi dallo spazio.";
            $msg = self::create_msg_error($errore);
        }   
        return $msg; 
    }

    static function check_input_img(string $img_path, int $upload_code): string {

        if($upload_code != UPLOAD_ERR_OK) {
            return self::create_msg_error("Errore durante il caricamento dell'immagine riprovare più tardi o contattare l'amministratore.");
        }

        $msg = '';
        $img_type = exif_imagetype($img_path);
       
        if(!$img_type) {
            $msg = self::create_msg_error("Il file caricato non è un immagine");
        }
        else if(!in_array($img_type, self::VALID_IMGS_EXT)) {
            $supp_ext = array_map(function($elemento) {
                return image_type_to_extension($elemento, false); 
            }, self::VALID_IMGS_EXT);
            $error = "L'immagine caricata non è in formato " . implode(", ", $supp_ext) . ".";
            $msg = self::create_msg_error($error);
        }
        return $msg;
    }
}