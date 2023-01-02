<?php
namespace App\Http\Resources\Akun;

use Illuminate\Http\Resources\Json\JsonResource;

class AkunResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'kode_akun'             => $this->kode_akun,
            'nama_akun'             => $this->nama_akun,
            'akun_utama'            => $this->akun_utama,
            'induk'                 => $this->akunInduk ?? null,
            'tipe_akun_id'          => $this->akunArray($this->tipe_akun),
            'tipe_akun'             => $this->tipe_akun,
            'arus_kas'              => $this->arus_kas,
            'arus_kas_tipe'         => $this->arus_kas_tipe ?? null,
            'arus_kas_tipe_text'    => $this->arusKasTipeArray($this->arus_kas_tipe),
            'default'               => $this->default,
            'default_text'          => $this->defaultArray($this->default),
            'transaksi'             => $this->transaksi,
            'id_user'               => $this->createdUser->id,
            'nama_user'             => $this->createdUser->nama ?? null
        ];
    }

    public function akunArray($name){
        $akun = [
            [
                'id'    => '1-',
                'name'  => 'AKTIVA'
            ],
            [
                'id'    => '2-',
                'name'  => 'KEWAJIBAN'
            ],
            [
                'id'    => '3-',
                'name'  => 'EKUITAS'
            ],
            [
                'id'    => '4-',
                'name'  => 'PENDAPATAN'
            ],
            [
                'id'    => '5-',
                'name'  => 'BEBAN'
            ]
        ];

        $key = array_search($name, array_column($akun, 'name'));
        return $akun[$key]['id'];
    }

    public function arusKasTipeArray($id){
        $akun = [
            [
                'id'    => 'operasional',
                'name'  => 'Operasional'
            ],
            [
                'id'    => 'investasi',
                'name'  => 'Investasi'
            ],
            [
                'id'    => 'pendanaan',
                'name'  => 'Pendanaan'
            ]
        ];

        $key = array_search($id, array_column($akun, 'id'));
        return $akun[$key]['name'];
    }

    public function defaultArray($id){
        $akun = [
            [
                'id'    => 'hpp',
                'name'  => 'HPP'
            ],
            [
                'id'    => 'persediaan',
                'name'  => 'Persediaan'
            ],
            [
                'id'    => 'tunai',
                'name'  => 'Transaksi Tunai'
            ],
            [
                'id'    => 'pembelian',
                'name'  => 'Pembelian'
            ],
            [
                'id'    => 'penjualan',
                'name'  => 'Penjualan'
            ],
            [
                'id'    => 'pembelian kredit',
                'name'  => 'Pembelian Kredit'
            ],
            [
                'id'    => 'penjualan kredit',
                'name'  => 'Penjualan Kredit'
            ],
            [
                'id'    => 'diskon pembelian',
                'name'  => 'Diskon Pembelian'
            ],
            [
                'id'    => 'diskon penjualan',
                'name'  => 'Diskon Penjualan'
            ],
        ];

        $key = array_search($id, array_column($akun, 'id'));
        return $akun[$key]['name'];
    }
}
