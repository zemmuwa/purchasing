<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>Document</title>
        <style>
            .color {
                color: red;
            }
            .table {
                border: 1px solid black;
            }
            tr td {
                border: 1px solid black;
            }
            .text-center {
                text-align: center;
            }
            .width100 {
                width: 100%;
            }
            .width25 {
                width: 25%;
            }
        </style>
    </head>
    <body>
		<h3 class="text-center">Rejected Your Draf RB/RB by $From</h3>
        <p class="text-center">Draf RB/RB Anda telah direject oleh ($From)</p>
		<% with $RB %>
		<p>Berikut Data Draf RB/RB $DraftRB.Kode/$Kode : </p>
		<p>Pemohon: {$DraftRB.Pemohon.Pegawai.Nama}</p>
		<p>Jabatan/Cabang: {$DraftRB.PegawaiPerJabatan.Jabatan.Nama}/$DraftRB.PegawaiPerJabatan.Cabang.Nama</p>
		<p>Jenis Permintaan: $DraftRB.Jenis</p>
		<p>Tanggal: $Top.FormatDate('d/m/Y',$DraftRB.Tgl)</p>
		<p>Tanggal Deadline: $Top.FormatDate('d/m/Y',$DraftRB.Deadline)</p>
		<% end_with %>
		<br>
		<p>Rincian Request Barang</p>

		<table border="1">
			<thead>
				<tr>
					<th>Jenis Barang</th>
					<th>Deskrips Kebutuhan</th>
					<th>Jumlah</th>
					<th>Satuan</th>
					<th>Spesifikasi Barang</th>
				</tr>
			</thead>
			<tbody>
				<% loop $RB.DraftRB.Detail %>
					<tr>
						<td>$Jenis.Nama</td>
						<td>$Deskripsi</td>
						<td>$Jumlah</td>
						<td>$Satuan.Nama</td>
						<td>$Spesifikasi</td>
					</tr>
				<% end_loop %>
			</tbody>
		</table>
	</body>
	<br>
	<p class="text-center">Klik link berikut untuk melihat Draf RB yang perlu Anda Approved</p>
	<p class="text-center"><a href="$Link">Klik disini</a></p>
</html>
