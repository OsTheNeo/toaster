<?php
/**
 * Created by PhpStorm.
 * User: Ostheneo
 * Date: 17/03/2017
 * Time: 4:24 PM
 */

namespace OsTheNeo\Toaster;

class Gallery {
    public static function icon($model, $id) {

        $bin = [$model => "$id"];
        $gallery = \App\Models\Store\Gallery::where('binded', json_encode($bin))->first();
        if ($gallery) {
            $path = explode(' ', $gallery->created_at);
            $path = explode('-', $path[0]);
            return ($path[0] . '/' . $path[1] . '/' . $gallery->icon);
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