<?php
/**
 * Created by PhpStorm.
 * User: Ostheneo
 * Date: 17/03/2017
 * Time: 4:24 PM
 */

namespace OsTheNeo\Toaster;

use OsTheNeo\Toaster\Models\Gallery as GalleryModel;

class Gallery {
    public static function icon($model, $id) {
        $folder = 'public/files/';
        $gallery = GalleryModel::where('binded', $model . '-' . $id)->first();
        if ($gallery) {
            $images = json_decode($gallery->images, true);
            if (sizeof($images) > 0) {
                $path = explode(' ', $gallery->created_at);
                $path = explode('-', $path[0]);
                return ($folder.$path[0] . '/' . $path[1] . '/' . $images[0]);
            } else {
                return $folder.'no-thumbnail.png';
            }
        } else {
            return $folder.'no-thumbnail.png';
        }

    }

    public static function viewGallery() {
    }

    public static function editGallery() {
    }


    public static function Gallery($model, $id) {


        $bin = [$model => "$id"];
        $gallery = \App\Models\Store\Gallery::where('binded', json_encode($bin))->first();

        if ($gallery) {
            $path = explode(' ', $gallery->created_at);
            $path = explode('-', $path[0]);

            if ($gallery->images) {
                $images = json_decode($gallery->images);

                $temp = [];

                foreach ($images as $image) {
                    $temp[] = $path[0] . '/' . $path[1] . '/' . $image;
                }

                return $temp;
            }
        }
    }


}