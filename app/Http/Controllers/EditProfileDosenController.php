<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\tb_dosen;
use App\Models\tb_kab;
use App\Models\tb_prov;
use App\Models\tb_temp_file;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Validation\Rule;

class EditProfileDosenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dosen = tb_dosen::where('nip', Auth::user()->nim_nip)->first();
        $provinsi = tb_prov::all();
        $kabupaten = tb_kab::where('kode_prov', $dosen->kode_prov)->get();

        return view('dosen.edit_profile', [
            'title' => 'Edit Profile',
        ])->with(compact('dosen', 'provinsi', 'kabupaten'));
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
        //
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate
        $request->validate([
            'fileProfile' =>
            [
                // required if fileProfile null
                Rule::requiredIf(function () {
                    return tb_dosen::where('nip', Auth::user()->nim_nip)->first()->foto == null;
                }),
            ],
            'nama' => 'required|string',
            'nip' => 'required',
            'status' => 'required',
            'handphone' => 'required|numeric',
            'email' =>
            [
                'required', 'email', 'max:255', Rule::unique('users')->ignore($id, 'nim_nip'),
            ],
            'alamat' => 'required',
            'provinsi' => 'required|exists:tb_provs,kode_prov',
            'kabupatenkota' => 'required|exists:tb_kabs,kode_kab',
        ]);

        $temp = tb_temp_file::where('path', $request->fileProfile)->first();

        // Update to DB
        tb_dosen::where('nip', $id)->update([
            'nama' => $request->nama,
            'handphone' => $request->handphone,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'kode_prov' => $request->provinsi,
            'kode_kab' => $request->kabupatenkota,
        ]);
        User::where('nim_nip', $id)->update([
            'nama' => $request->nama,
            'email' => $request->email,
        ]);
        if ($request->fileProfile != null && tb_dosen::where('nip', $id)->first()->foto != null) {
            unlink(tb_dosen::where('nip', $id)->first()->foto);
        }
        if ($temp && $request->fileProfile != null) {
            $uniq = time() . uniqid();
            rename(public_path('files/temp/' . $temp->path), public_path('files/profile/' . $id . '_' . $uniq . '.jpg'));
            tb_dosen::where('nip', $id)->update([
                'foto' => 'files/profile/' . $id . '_' . $uniq . '.jpg',
            ]);
            $temp->delete();
        }

        Alert::success('Berhasil', 'Data berhasil disimpan');
        return redirect()->route('home');
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
