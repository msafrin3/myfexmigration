<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Restructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restructure:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $forms = ['f_sect14_1', 'f_sect54_1', 'f_sect55_1', 'f_sect6_1', 'f_sect88_1'];
        $fm_csv = DB::connection('myfex_v1_latest')->table('fm_csv')->where('csv_data', '!=', null)->where('csv_data', '!=', '')->whereIn('form_name', $forms)->get();

        foreach($fm_csv as $fm) {
            // dd($fm->csv_data);
            $cols1 = [
                'pid' => $fm->pid,
                'form_name' => $fm->form_name,
                'update_date' => $fm->update_date,
                'csv_data' => $fm->csv_data,
                'flag' => $fm->flag,
                'flag_year' => $fm->flag_year,
            ];
            $cols2 = $this->parse_csv($fm->csv_data);
            $cols = (array) array_merge((array) $cols1, (array) $cols2);

            if($fm->form_name == 'f_sect14_1') {
                $cols['section'] = 14;
                // array_push($f_sect14, $cols);
            } elseif($fm->form_name == 'f_sect54_1') {
                $cols['section'] = 54;
                // array_push($f_sect54, $cols);
            } elseif($fm->form_name == 'f_sect55_1') {
                $cols['section'] = 55;
                // array_push($f_sect55, $cols);
            } elseif($fm->form_name == 'f_sect6_1') {
                $cols['section'] = 6;
                // array_push($f_sect6, $cols);
            } elseif($fm->form_name == 'f_sect88_1') {
                $cols['section'] = 88;
                // array_push($f_sect88, $cols);
            }
            try {
                $data = $this->removeNonColumn('fm_csv_clean', $cols);
                DB::connection('myfex_v1_latest')->table('fm_csv_clean')->insert($data);
            } catch(\Exception $e) {
                echo "Error: ".$e->getMessage()."\n";
            }
        }

        echo "Completed!";
        
        // $this->print("Section 14: ".count($f_sect14));
        // $this->print("Section 54: ".count($f_sect54));
        // $this->print("Section 55: ".count($f_sect55));
        // $this->print("Section 6: ".count($f_sect6));
        // $this->print("Section 88: ".count($f_sect88));
    }

    public static function parse_csv($file, $content = array(), $comma = ',', $quote = '"', $newline = "\n") {
        $db_quote = $quote . $quote;
//        $file = $file;
//        echo '<pre>';
//        print_r($file);
//        echo '</pre>';
//        exit; 
        $file = trim($file);
        $file = str_replace("\r\n", $newline, $file);
        $file = str_replace($db_quote, '&quot;', $file);
        $file = str_replace(',&quot;,', ',,', $file);
        $file .= $comma;

        $inquotes = false;
        $start_point = 0;
        $row = 0;

        for ($i = 0; $i < strlen($file); $i++) {
            $char = $file[$i];
            if ($char == $quote) {
                if ($inquotes) {
                    $inquotes = false;
                } else {
                    $inquotes = true;
                }
            }

            if (($char == $comma or $char == $newline) and ! $inquotes) {
                $cell = substr($file, $start_point, $i - $start_point);
                $cell = str_replace($quote, '', $cell);
                $cell = str_replace('&quot;', $quote, $cell);
                $cell = str_replace('"', '', $cell);
                $handle[$row][] = $cell;
                $start_point = $i + 1;
                if ($char == $newline) {
                    $row++;
                }
            }
        }

        foreach ($handle as $data) {
//            echo '<pre>';
//            print_r($data);
//            echo '</pre>';
//            exit;
            $key = $data;
            if ($data[0] == 'array') {
                $val = array();
                unset($data[0]);
                unset($data[1]);
                foreach ($data as $d) {
                    $val[] = $d;
                }
            } else {
//                $val = $data;
                $val = isset($data[2]) ? $data[2] : '';
                $val = str_replace('&qqq;', '"', $val);
                $val = str_replace('&xxx;', "'", $val);
                $val = isset($val) ? $val : '';
            }
            $xx = isset($key[1]) ? $key[1] : '';
            $content[$xx] = $val;
        }

        return $content;
    }

    // public function handle2()
    // {
    //     $forms = ['f_sect14_1', 'f_sect54_1', 'f_sect55_1', 'f_sect6_1', 'f_sect88_1'];
    //     $fm_csv = DB::connection('myfex_v1_latest')->table('fm_csv')->where('csv_data', '!=', null)->where('csv_data', '!=', '')->whereIn('form_name', $forms)->get();

    //     foreach($fm_csv as $fm) {
    //         $row = explode("\"\n", $fm->csv_data);
    //         $cols = [
    //             'pid' => $fm->pid,
    //             'form_name' => $fm->form_name,
    //             'update_date' => $fm->update_date,
    //             'csv_data' => $fm->csv_data,
    //             'flag' => $fm->flag,
    //             'flag_year' => $fm->flag_year,
    //         ];
    //         foreach($row as $r) {
    //             $pattern = "/(.*),\"(.*)\",\"(.*)\"/";
    //             preg_match($pattern, $r, $output);
    //             // var_dump($output);
    //             if(count($output) > 0) {
    //                 $cols[$output[2]] = $output[3];
    //             }
    //         }
    //         if($fm->form_name == 'f_sect14_1') {
    //             $cols['section'] = 14;
    //             // array_push($f_sect14, $cols);
    //         } elseif($fm->form_name == 'f_sect54_1') {
    //             $cols['section'] = 54;
    //             // array_push($f_sect54, $cols);
    //         } elseif($fm->form_name == 'f_sect55_1') {
    //             $cols['section'] = 55;
    //             // array_push($f_sect55, $cols);
    //         } elseif($fm->form_name == 'f_sect6_1') {
    //             $cols['section'] = 6;
    //             // array_push($f_sect6, $cols);
    //         } elseif($fm->form_name == 'f_sect88_1') {
    //             $cols['section'] = 88;
    //             // array_push($f_sect88, $cols);
    //         }
    //         try {
    //             $data = $this->removeNonColumn('fm_csv_clean', $cols);
    //             DB::connection('myfex_v1_latest')->table('fm_csv_clean')->insert($data);
    //         } catch(\Exception $e) {
    //             echo "Error: ".$e->getMessage()."\n";
    //         }
    //     }

    //     echo "Completed!";
        
    //     // $this->print("Section 14: ".count($f_sect14));
    //     // $this->print("Section 54: ".count($f_sect54));
    //     // $this->print("Section 55: ".count($f_sect55));
    //     // $this->print("Section 6: ".count($f_sect6));
    //     // $this->print("Section 88: ".count($f_sect88));
    // }

    public function print($text) {
        echo "\e[0;34m".$text."\e[0m\n";
    }

    public function isColumnExist($table, $column) {
        $check = Schema::hasColumn($table, $column);
        return $check;
    }

    public function removeNonColumn($table, $data) {
        foreach($data as $index => $value) {
            if(Schema::hasColumn($table, $index)) {
                if($value == '') {
                    $data[$index] = null;
                }
            } else {
                unset($data[$index]);
            }
        }
        return $data;
    }
}
