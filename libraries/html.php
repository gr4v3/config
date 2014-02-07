<?php
defined( '_CONFIGWEBSERVICE' ) or die( 'Restricted access' );
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Html extends Load {
    function __construct() {
        parent::__construct();
    }
    public function thumbnails($path = NULL, $width = 100, $height = 100) {
        if (empty($path)) return FALSE;
        //check if folder resize does exist
        $root_path = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
        $img_name = str_replace('images/','',$path);
        $img_original_path = $root_path . 'images'. DS . $img_name;
        $img_resized_path  = $root_path . 'images'. DS . 'thumbnails' . DS . $img_name;
        $img = new JImage($img_original_path);
        $properties = JImage::getImageFileProperties($img_original_path);
        $mime = $properties->mime;
        if ($mime == 'image/jpeg') $type = IMAGETYPE_JPEG;
        elseif ($mime = 'image/png') $type = IMAGETYPE_PNG;
        elseif ($mime = 'image/gif') $type = IMAGETYPE_GIF;
        $resized = $img->resize($width,$height, TRUE);
        $resized->toFile($img_resized_path, $type);
        return 'images/thumbnails/' . $img_name;
    }
}
?>
