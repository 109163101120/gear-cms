<?php

class media {

    public static function getUniqueName($path, $filename) {

        if($pos = strrpos($filename, '.')) {
            $name = substr($filename, 0, $pos);
            $ext = substr($filename, $pos);
        } else {
            $name = $filename;
        }

        $newpath = $path.'/'.$filename;
        $newname = $filename;
        $counter = 0;

        while (file_exists($newpath)) {
            $newname = $name .'_'. $counter . $ext;
            $newpath = $path.'/'.$newname;
            $counter++;
        }

        return $newname;

    }

    public static function size($path) {

        $size = filesize($path);

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = ($size > 0) ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

    }

    public static function getAll($path) {

        $dirs = [];
        $files = [];

        $array = array_diff(scandir(dir::media($path)), array('.', '..'));

        foreach($array as $name) {

            $file = $path.$name;

            if(is_dir(dir::media($file))) {
                $dirs[] = [
                    'name' => $name,
                    'path' => str_replace(dir::media(), '', $file."/"),
                    'size' => '',
                    'type' => 'dir'
                ];
            } else {
                $files[] = [
                    'name' => $name,
                    'size' => self::size(dir::media($file)),
                    'type' => 'file'
                ];
            }

        }

        return array_merge($dirs, $files);

    }

}

?>
