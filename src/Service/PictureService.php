<?php
    namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function PHPUnit\Framework\fileExists;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params=$params;
    }

    public function add(UploadedFile $picture ,?string $folder='',?int $width=250,?int $height=250)
    {
        
        //on donne un nouveau nom a l'image
        $fichier=md5(uniqid(rand(),true)).'.webp';
        
        //on récupère les infos de l'image
        $pictureInfos=getimagesize($picture);
        
        if ($pictureInfos===false) {
            throw new Exception('format d\'image incorrecte !');
        }

        //on vérifie le format d'image jpg ......
        switch ($pictureInfos['mime']) {
            case 'image/png':
                $picture_source = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $picture_source = imagecreatefromjpeg($picture);

                break;
            case 'image/webp':
                $picture_source = ImageCreateFromwebp($picture);
                break;
            default:
                throw new Exception('format d\'image incorrecte !');
        }
        //on encader l'image
        //on récupère les dimension
        $imageWidth=$pictureInfos[0];
        $imageHeight=$pictureInfos[1];
        //on vérifie l'orientation
        switch ($imageWidth<=>$imageHeight) {
            case -1: //portrait
                $squareSize=$imageWidth;
                $src_x =0;
                $src_y=($imageHeight - $squareSize) / 2 ; 
                break;

            case 0: //carré
                $squareSize=$imageWidth;
                $src_x = 0;
                $src_y = 0 ; 
                break;

            case 1: //paysage
                $squareSize=$imageHeight;
                $src_x = ($imageWidth - $squareSize) / 2 ; 
                $src_y = 0;
                break;
            
            default:
                # code...
                break;
        }
        //on cree un image vierge
        $resized_picture = imagecreatetruecolor($width,$height);
        
        imagecopyresampled($resized_picture,$picture_source, 0, 0,$src_x,$src_y,$width,$height,$squareSize,$squareSize); 
        $path=$this->params->get('image_directory'). $folder;


        //on cree le dossier de destination s'il n'existe pas 
        
        if (!file_exists($path.'/mini/')) {
     
            mkdir($path.'/mini/',0755,true);
            
           
        }
        //on stock l'image recadré
        imagewebp($resized_picture,$path.'/mini/'.$width.'x'.$height.'-'.$fichier);
        $picture->move($path.'/',$fichier);
        return $fichier;
    }
    public function delete(string $fichier,?string $folder='',?int $width=250,?int $height=250)
    {
        if ($fichier!=='default.webp') {
            $seccess=false;
            $path=$this->params->get('image_directory'). $folder;
            $mini=$path.'/mini/'.$width.'x'.$height.'-'.$fichier;
            if (file_exists($mini)) {
                unlink($mini);
                $seccess=true;
            }
    
            $original=$path.'/'.$fichier;
            if (file_exists($original)) {
                unlink($original);
                $seccess=true;
            }
            return $seccess;
        }
        return false;  
    }
   
}