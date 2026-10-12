<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    private function aturan(): array
    {
        return [
            'judul'       => 'required|string|max:150',
            'mata_kuliah' => 'nullable|string|max:100',
            'tenggat'     => 'required|date_format:Y-m-d H:i:s',
            'prioritas'   => 'required|in:rendah,sedang,tinggi',
            'selesai'     => 'sometimes|boolean',
            'catatan'     => 'nullable|string|max:1000',
        ];
    }

    // Belum selesai di atas, diurutkan dari tenggat terdekat.
    public function index()
    {
        $data = Tugas::orderBy('selesai')->orderBy('tenggat')->orderBy('id')->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan());
        $data['selesai'] = $data['selesai'] ?? false;
        $data['selesai_pada'] = $data['selesai'] ? now() : null;

        $tugas = Tugas::create($data);

        return response()->json($tugas, 201);
    }

    public function update(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);
        $data = $request->validate($this->aturan());

        if (array_key_exists('selesai', $data)) {
            if ($data['selesai'] && ! $tugas->selesai) {
                $data['selesai_pada'] = now();
            } elseif (! $data['selesai']) {
                $data['selesai_pada'] = null;
            }
        }

        $tugas->update($data);

        return response()->json($tugas);
    }

    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);
        if ($tugas->foto) {
            Storage::disk('local')->delete($tugas->foto);
        }
        $tugas->delete();

        return response()->json(['pesan' => 'Tugas dihapus']);
    }

    // Unggah (atau ganti) foto lampiran. Dikirim sebagai multipart dengan field "foto".
    public function unggahFoto(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);
        $request->validate(['foto' => 'required|image|max:8192']);

        if ($tugas->foto) {
            Storage::disk('local')->delete($tugas->foto);
        }
        $tugas->foto = Storage::disk('local')->putFile('foto', $request->file('foto'));
        $tugas->save();

        return response()->json($tugas->fresh());
    }

    public function hapusFoto($id)
    {
        $tugas = Tugas::findOrFail($id);
        if ($tugas->foto) {
            Storage::disk('local')->delete($tugas->foto);
            $tugas->foto = null;
            $tugas->save();
        }

        return response()->json($tugas->fresh());
    }

    public function lihatFoto($id)
    {
        $tugas = Tugas::findOrFail($id);
        abort_unless($tugas->foto && Storage::disk('local')->exists($tugas->foto), 404);

        return response()->file(Storage::disk('local')->path($tugas->foto));
    }

    public function statistik()
    {
        $total = Tugas::count();
        $selesai = Tugas::where('selesai', true)->count();
        $terlambat = Tugas::where('selesai', false)->where('tenggat', '<', now())->count();
        $tepatWaktu = Tugas::where('selesai', true)
            ->whereColumn('selesai_pada', '<=', 'tenggat')
            ->count();

        $namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $harian = [];
        for ($i = 6; $i >= 0; $i--) {
            $hari = now()->subDays($i);
            $harian[] = [
                'tanggal' => $hari->toDateString(),
                'label'   => $namaHari[$hari->dayOfWeek],
                'jumlah'  => Tugas::where('selesai', true)->whereDate('selesai_pada', $hari->toDateString())->count(),
            ];
        }

        return response()->json([
            'total'              => $total,
            'selesai'            => $selesai,
            'aktif'              => $total - $selesai,
            'terlambat'          => $terlambat,
            'tepat_waktu'        => $tepatWaktu,
            'persen_tepat_waktu' => $selesai > 0 ? (int) round($tepatWaktu / $selesai * 100) : 0,
            'harian'             => $harian,
        ]);
    }
}
