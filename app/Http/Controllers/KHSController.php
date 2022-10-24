<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\tb_dosen;
use App\Models\tb_entry_progress;
use App\Models\tb_mahasiswa;
use App\Models\tb_irs;
use App\Models\tb_khs;
use App\Models\tb_pkl;
use App\Models\tb_skripsi;
use App\Models\tb_temp_file;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class KHSController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $countSemsester = tb_entry_progress::where('nim', Auth::user()->nim_nip)->count();
        $progress = tb_entry_progress::where('nim', Auth::user()->nim_nip)
            ->where('semester_aktif', $countSemsester)->first();
        $mahasiswa = tb_mahasiswa::where('nim', Auth::user()->nim_nip)->first();
        $khs = tb_khs::where('nim', Auth::user()->nim_nip)->get();
        return view('mahasiswa.khs.entry', [
            'title' => 'Entry KHS',
        ])->with(compact('mahasiswa', 'khs', 'progress'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function data()
    {
        $progress = tb_entry_progress::where('nim', Auth::user()->nim_nip)->first();
        $mahasiswa = tb_mahasiswa::where('nim', Auth::user()->nim_nip)->first();
        $khs = tb_khs::where('nim', Auth::user()->nim_nip)->get();
        return view('mahasiswa.khs.index', [
            'title' => 'KHS',
        ])->with(compact('mahasiswa', 'khs', 'progress'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate
        $request->validate([
            'semester_aktif' => 'required|unique:tb_khs,semester_aktif,NULL,id,nim,' . Auth::user()->nim_nip,
            'sks_semester' => 'required|numeric',
            'sks_kumulatif' => 'required|numeric',
            'ip_semester' => 'required|between:0,4.00',
            'ip_kumulatif' => 'required|between:0,4.00',
            'file' => 'required',
        ]);

        $temp = tb_temp_file::where('path', $request->file)->first();

        // Insert to DB
        $db = tb_khs::create([
            'nim' => Auth::user()->nim_nip,
            'semester_aktif' => $request->semester_aktif,
            'sks' => $request->sks_semester,
            'sks_kumulatif' => $request->sks_kumulatif,
            'ip' => $request->ip_semester,
            'ip_kumulatif' => $request->ip_kumulatif,
            'upload_khs' => $temp->path,
        ]);

        tb_entry_progress::where('nim', Auth::user()->nim_nip)
            ->where('semester_aktif', $request->semester_aktif)
            ->update([
                'is_khs' => 1,
            ]);

        if ($temp) {
            $uniq = time() . uniqid();
            rename(public_path('files/temp/' . $temp->folder . '/' . $temp->path), public_path('files/khs/' . $uniq . '_' . $db->nim . '_' . $db->semester_aktif . '.pdf'));
            rmdir(public_path('files/temp/' . $temp->folder));
            $db->where('semester_aktif', $request->semester_aktif)->update([
                'upload_khs' => 'files/khs/' . $uniq . '_' . $db->nim . '_' . $db->semester_aktif . '.pdf'
            ]);
            $temp->delete();
        }

        if ($db->save()) {
            Alert::success('Berhasil', 'Data berhasil disimpan');
            return redirect()->route('pkl.index');
        } else {
            Alert::error('Gagal', 'Data gagal disimpan');
            return redirect()->route('khs.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($semester_aktif, $nim)
    {
        $data = tb_khs::where('nim', $nim)->where('semester_aktif', $semester_aktif)->first();
        return view('mahasiswa.khs.modal', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $semester_aktif)
    {
        // Validate
        $request->validate([
            'sks_semester' => 'required|numeric',
            'sks_kumulatif' => 'required|numeric',
            'ip_semester' => 'required|between:0,4.00',
            'ip_kumulatif' => 'required|between:0,4.00',
            'confirm' => 'sometimes|accepted',
            'fileEdit' => 'required_if:confirm,on',
        ]);

        $db = tb_khs::where('semester_aktif', $semester_aktif)->where('nim', $request->nim)->first();

        $temp = tb_temp_file::where('path', $request->fileEdit)->first();

        if ($temp && $request->confirm == 'on') {
            $uniq = time() . uniqid();
            rename(public_path('files/temp/' . $temp->folder . '/' . $temp->path), public_path('files/khs/' . $uniq . '_' . $db->nim . '_' . $db->semester_aktif . '.pdf'));
            rmdir(public_path('files/temp/' . $temp->folder));
            tb_khs::where('semester_aktif', $semester_aktif)->where('nim', $request->nim)->update([
                'sks' => $request->sks_semester,
                'sks_kumulatif' => $request->sks_kumulatif,
                'ip' => $request->ip_semester,
                'ip_kumulatif' => $request->ip_kumulatif,
                'upload_khs' => 'files/khs/' . $uniq . '_' . $db->nim . '_' . $db->semester_aktif . '.pdf'
            ]);
            unlink(public_path($db->upload_khs));
        } else {
            tb_khs::where('semester_aktif', $semester_aktif)->where('nim', $request->nim)->update([
                'sks' => $request->sks_semester,
                'sks_kumulatif' => $request->sks_kumulatif,
                'ip' => $request->ip_semester,
                'ip_kumulatif' => $request->ip_kumulatif,
            ]);
        }

        if ($db->update()) {
            Alert::success('Berhasil', 'Data berhasil diubah');
            return redirect('/mahasiswa/data/khs');
        } else {
            Alert::error('Gagal', 'Data gagal diubah');
            return redirect('/mahasiswa/data/khs');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
