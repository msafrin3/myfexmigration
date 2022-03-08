<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

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
        $dataset = [];
        $fm_csv = DB::connection('myfex1')->table('fm_csv')->where('csv_data', '!=', null)->where('csv_data', '!=', '')->whereIn('form_name', $forms)->get();
        // sections
        $f_sect14 = [];
        $f_sect54 = [];
        $f_sect55 = [];
        $f_sect6 = [];
        $f_sect88 = [];

        foreach($fm_csv as $fm) {
            $row = explode("\n", $fm->csv_data);
            $cols = [
                'pid' => $fm->pid,
                'form_name' => $fm->form_name,
                'update_date' => $fm->update_date,
                'flag' => $fm->flag,
                'flag_year' => $fm->flag_year
            ];
            foreach($row as $r) {
                $pattern = "/(.*),\"(.*)\",\"(.*)\"/";
                preg_match($pattern, $r, $output);
                // var_dump($output);
                if(count($output) > 0) {
                    $cols[$output[2]] = $output[3];
                }
            }
            if($fm->form_name == 'f_sect14_1') {
                array_push($f_sect14, $cols);
            } elseif($fm->form_name == 'f_sect54_1') {
                array_push($f_sect54, $cols);
            } elseif($fm->form_name == 'f_sect55_1') {
                array_push($f_sect55, $cols);
            } elseif($fm->form_name == 'f_sect6_1') {
                array_push($f_sect6, $cols);
            } elseif($fm->form_name == 'f_sect88_1') {
                array_push($f_sect88, $cols);
            }
        }
        
        $this->print("Section 14: ".count($f_sect14));
        $this->print("Section 54: ".count($f_sect54));
        $this->print("Section 55: ".count($f_sect55));
        $this->print("Section 6: ".count($f_sect6));
        $this->print("Section 88: ".count($f_sect88));
    }

    public function print($text) {
        echo "\e[0;34m".$text."\e[0m\n";
    }
}
