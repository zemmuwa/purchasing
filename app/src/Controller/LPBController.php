<?php

use SilverStripe\Assets\Upload;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\Member;


class LPBController extends PageController
{
    private static $allowed_actions = [
        'getDetailBarang', 'ApprovePage', 'searchlpb', 'getData', 'doPostLPB', 'view', 'tes'
    ];

    public function tes() {
        $member = Member::get()->byID(8)->checkPassword("12345");
        var_dump($member->isValid());
        // $tes = new Authenticator();
        // var_dump($tes->checkPassword($member, "12345678"));
    }

    public function view(HTTPRequest $request) {
        if (isset($request->params()["ID"])) {
            $id = $request->params()["ID"];
            $data = [
                "LPB" => LPB::get()->ByID($id),
                "mgeJS" => "lpb"
            ];

            return $this->customise($data)
            ->renderWith(array(
                'LPBView', 'Page',
            ));
        }
    }

    public function index(HTTPRequest $request)
    {
        $data = [
            'cur_status' => 'Me',
            'user' => 'User',
            'Columns' => new ArrayList($this->getCustomColumns('lpb')),
            'siteParent'=>"List LPB",
            // 'linkNew'=>"lpb/ApprovePage",
            "mgeJS" => "list-lpb"
        ];

        return $this->customise($data)
        ->renderWith(array(
            'ListLPBPage', 'Page',
        ));
    }

    public function doPostLPB()
    {
        // var_dump($_REQUEST['tgl-lpb']);die;
        if (isset($_REQUEST['tgl-lpb']) && $_REQUEST['tgl-lpb'] != "") {
            $note = "";

            if (isset($_REQUEST['note']) && $_REQUEST['note'] != "")
                $note = $_REQUEST['note'];
            $tgl = $_REQUEST['tgl-lpb'];

            $po = new LPB();
            $po->Tgl = AddOn::convertDateToDatabase($tgl);
            $po->POID = $_REQUEST['POID'];
            $po->Note = $note;
            $idPO = $po->write();

            $jenisBarang = $_REQUEST['jenis_barangid'];
            $namaBarang = $_REQUEST['nama_barang'];
            $jumlah = $_REQUEST['jumlah'];
            $jumlahDiterima = $_REQUEST['jumlah_diterima'];
            $satuan = $_REQUEST['satuanid'];
            $harga = $_REQUEST['harga'];
            $subtotal = $_REQUEST['subtotal'];
            $parentid = $_REQUEST['parentid'];
            $detailPerSupplier = $_REQUEST['detail_sup'];

            foreach ($jenisBarang as $key => $val) {
                $poDetail = new LPBDetail();

                $poDetail->JenisID = $jenisBarang[$key];
                $poDetail->NamaBarang = $namaBarang[$key];
                $poDetail->Jumlah = AddOn::unformatNumber($jumlah[$key]);
                $poDetail->JumlahTerima = AddOn::unformatNumber($jumlahDiterima[$key]);
                $poDetail->SatuanID = $satuan[$key];
                $poDetail->Harga = AddOn::unformatNumber($harga[$key]);
                $poDetail->Total = AddOn::unformatNumber($subtotal[$key]);
                $poDetail->LPBID = $idPO;
                $poDetail->DetailPOID = $parentid[$key];
                $poDetail->DetailPerSupplierID = $detailPerSupplier[$key];

                $poDetail->write();
            }

            return $this->redirect(Director::absoluteBaseURL()."ta");
        }
    }

    public function ApprovePage(HTTPRequest $request)
    {
        if (isset($request->params()["ID"])) {
            $id = $request->params()["ID"];

            $detail = PODetail::get()->where("POID = {$id}");
            $total = 0;
            foreach ($detail as $val) {
                $total += (double) $val->Total;
            };

            $data = array(
                "PO" => PO::get()->byID($id),
                "Detail" => $detail,
                "Supplier" => Supplier::get(),
                "TotalAkhir" => $total,
                "mgeJS" => "lpb"
            );
            return $this->customise($data)
                ->renderWith(array(
                    'LPBPage', 'Page',
                ));
        }
    }

    function getData(){
        $jenis = isset($_REQUEST['jenis']) ? $_REQUEST['jenis'] : '';
        $msg = [];
        $data = [];

        $user_logged_id = $_SESSION['user_id'];

        if (!empty($jenis)) {
            switch ($jenis) {
                case 'kdlpb':
                    $data = AddOn::getSpecColumn(LPB::get()->toNestedArray(), 'Kode');
                    break;
                case 'kdpo':
                    $data = AddOn::getSpecColumn(PO::get()->toNestedArray(), 'Kode');
                    break;
                case 'kdrb':
                    $data = AddOn::getSpecColumn(RB::get()->toNestedArray(), 'Kode');
                    break;
                case 'kddrb':
                    $data = AddOn::getSpecColumn(DraftRB::get()->toNestedArray(), 'Kode');
                    break;
                case 'vendor':
                    $data = Supplier::get()->map('ID', 'Nama')->toArray();
                    break;
                default:
                    $data = 'Jenis tidak ada';
                    break;
            }
            $msg = [
                'status' => TRUE,
                'msg' => $data
            ];
        }else{
            $msg = [
                'status' => FALSE,
                'msg' => 'Jenis cannot be empty'
            ];
        }

        echo json_encode($msg);
    }

    /**
         * Setting column definition
         */
        function getCustomColumns($config) {
            $columns = array();

            switch ($config) {
                case 'lpb':
                    $columns = array(
                        array(
                            'ColumnTb' => 'Kode LPB',
                            'ColumnDb' => 'Kode',
                            'Type' => 'Varchar',
                            'Required' => true
                        ),
                        array(
                            'ColumnTb' => 'Kode PO',
                            'ColumnDb' => 'kodepo',
                            'Type' => 'Varchar',
                            'Required' => true
                        ),
                        array(
                            'ColumnTb' => 'Kode Rb',
                            'ColumnDb' => 'koderb',
                            'Type' => 'Varchar',
                            'Required' => true
                        ),
                        array(
                            'ColumnTb' => 'Kode Draft Rb',
                            'ColumnDb' => 'kodedrb',
                            'Type' => 'Varchar',
                            'Required' => true
                        ),
                        array(
                            'ColumnTb' => 'Tanggal',
                            'ColumnDb' => 'Tgl',
                            'Type' => 'Date',
                            'Required' => true
                        ),
                        array(
                            'ColumnTb' => 'Supplier',
                            'ColumnDb' => 'supplier',
                            'Type' => 'Varchar',
                            'Required' => true
                        )
                    );
                    break;
                default:
                    # code...
                    break;
            }

            return $columns;
        }

    function searchlpb() {
        // ============ filter bawaan datatable
        $pk = 'ID';

        $filter_record = (isset($_REQUEST['filter_record'])) ? $_REQUEST['filter_record'] : '';
        // $cur_status = isset($_REQUEST['cur_status']) ? $_REQUEST['cur_status'] : 'Me';
        $start = (isset($_REQUEST['start'])) ? $_REQUEST['start'] : 0;
        $length = (isset($_REQUEST['length'])) ? $_REQUEST['length'] : 10;
        $search = (isset($_REQUEST['search']['value'])) ? $_REQUEST['search']['value'] : '';
        $columnsort = (isset($_REQUEST['order'][0]['column'])) ? $_REQUEST['order'][0]['column'] : 0;
        $typesort = (isset($_REQUEST['order'][0]['dir'])) ? $_REQUEST['order'][0]['dir'] : 'DESC';
        $fieldsort = (isset($_REQUEST['columns'][$columnsort]['data']) && $_REQUEST['columns'][$columnsort]['data']) ? $_REQUEST['columns'][$columnsort]['data'] : 0;
        // ============ end filter

        // params
        $user_logged_id = $_SESSION['user_id'];

        // SETTING INI
        $columns = $this->getCustomColumns('lpb');
        $params_array = [];
        parse_str($filter_record, $params_array);

        $kodeLPB = (isset($params_array['kodeLpb'])) ? $params_array['kodeLpb'] : "";
        $kodePO = (isset($params_array['kodePo'])) ? $params_array['kodePo'] : "";
        $kodeRB = (isset($params_array['kodeRb'])) ? $params_array['kodeRb'] : "";
        $kodeDRB = (isset($params_array['kodeDraftRb'])) ? $params_array['kodeDraftRb'] : "";
        $vendor = (isset($params_array['vendor'])) ? $params_array['vendor'] : "";
        $tgl = (isset($params_array['tgl'])) ? $params_array['tgl'] : "";

        $result = LPB::get();

        // var_dump($kodePO);die;

        if ($kodeLPB != "") {
            $result = $result->where("Kode LIKE '%{$kodeLPB}%'");
        }
        if ($kodePO != "") {
            $result = $result
            ->innerJoin("po", "\"po\".\"ID\" = \"lpb\".\"POID\"")
            ->where("po.Kode LIKE '%{$kodePO}%'");
        }
        if ($kodeRB != "") {
            $result = $result
            ->innerJoin("po", "\"po\".\"ID\" = \"lpb\".\"POID\"")
            ->innerJoin("rb", "\"po\".\"RBID\" = \"rb\".\"ID\"")
            ->where("rb.Kode LIKE '%{$kodeRB}%'");
        }
        if ($kodeDRB != "") {
            $result = $result
            ->innerJoin("po", "\"po\".\"ID\" = \"lpb\".\"POID\"")
            ->innerJoin("rb", "\"po\".\"RBID\" = \"rb\".\"ID\"")
            ->innerJoin("draftrb", "\"rb\".\"DraftRBID\" = \"draftrb\".\"ID\"")
            ->where("draftrb.Kode LIKE '%{$kodeDRB}%'");
        }
        if ($tgl != "") {
            $tgl = PageController::FormatDate("Y-m-d H:i:s", $tgl);
            $result = $result->where("Tgl LIKE '%{$tgl}%'");
        }
        if ($vendor != "") {
            $result = $result
            ->innerJoin("po", "\"po\".\"ID\" = \"lpb\".\"POID\"")
            ->where("po.NamaSupplier LIKE '%{$vendor}%'");
        }

        // count all data (by filter otherwise)
        $count_all = LPB::get()->count();


        $column_sorted = $columns[$fieldsort]['ColumnDb'];
			$result = $result->sort($column_sorted . ' ' . $typesort);
			if ($column_sorted == "kodepo") {
				$result = $result->sort(['PO.Kode' => $typesort]);
			}
			if ($column_sorted == "koderb") {
				$result = $result->sort(['PO.RB.Kode' => $typesort]);
			}
			if ($column_sorted == "kodedrb") {
				$result = $result->sort(['PO.RB.DraftRB.Kode' => $typesort]);
            }
            if ($column_sorted == "supplier") {
				$result = $result->sort(['PO.NamaSupplier' => $typesort]);
			}
			// count all data (by filter otherwise)
			$count_all = $result->count();

			// limit offset
			$result = $result->limit($length, $start);

        $arr = array();
        foreach ($result as $row) {
            $temp = array();

            $idx = 0;

            $id = $row->{$pk};

            foreach ($columns as $col) {
                if ($col['ColumnDb'] == 'kodepo') {
                    $kode = $row->PO()->Kode;
                    $temp[] = $kode;
                }elseif($col['Type'] == 'Date'){
						$temp[] = date('d-m-Y', strtotime($row->{$col['ColumnDb']}));
                }elseif ($col['ColumnDb'] == 'koderb') {
                    $kode = $row->PO()->RB()->Kode;
                    $temp[] = $kode;
                }elseif ($col['ColumnDb'] == 'kodedrb') {
                    $kode = $row->PO()->RB()->DraftRB()->Kode;
                    $temp[] = $kode;
                }elseif ($col['ColumnDb'] == 'supplier') {
                    $kode = $row->PO()->NamaSupplier;
                    $temp[] = $kode;
                }else
                    $temp[] = $row->{$col['ColumnDb']};

                $idx++;
            }


            $view_link = Director::absoluteBaseURL().'lpb/view/'.$id;
            $delete_link = $this->Link().'deletereqcost/'.$id;

            $temp[] = '
            <div class="btn-group">
              <a href="'.$view_link.'" type="button" class="btn btn-default view"><i class="text-info fa fa-eye"></i> View</a>
              <!--<a href="'.$delete_link.'" type="button" class="btn btn-danger delete"><i class="text-info fa fa-eye"></i> Delete</a>-->
            </div>
            ';

            $arr[] = $temp;
        }

        // die;
        $result = array(
            'data' => $arr,
            'column_sort'=> $column_sorted,
            'params_arr' => $params_array,
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_all,
            'sql' => $result['sql']
        );
        return json_encode($result);
    }

    public function getDetailBarang()
    {
        if (
            isset($_REQUEST['nama_supplier']) &&  $_REQUEST['nama_supplier'] != ""
            && isset($_REQUEST['id_po']) &&  $_REQUEST['id_po'] != ""
        ) {
            $data = DraftRBDetail::get()->where("DraftRBID = 1");
            $arr = array();

            foreach ($data as $val) {
                $arr[] = array(
                    'ID' => $val->ID,
                    'JenisBarang' => $val->Jenis()->Nama,
                    'NamaBarang' => "tes",
                    'JumlahBarang' => $val->Jumlah,
                    'Satuan' => $val->Satuan()->Nama,
                    'Harga' => "100",
                    'Diskon' => '10',
                    'DiskonRP' => '100',
                    'Subtotal' => '1000',
                );
            }
            return json_encode($arr);
        }
    }
}
