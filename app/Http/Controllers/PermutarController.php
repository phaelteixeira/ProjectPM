<?php

namespace App\Http\Controllers;

use App\Permutar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermutarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permutas = Permutar::all();
        return view ('permuta/listaEspera', compact('permutas'));
    }

    public function indexer()
    {
        $permutas = Permutar::all();
        return view ('permuta/listaAceita', compact('permutas'));
    }

    public function teste()
    {
        $permutas = Permutar::all();
        return view('permuta/permuta', compact('permutas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('permuta/telapermuta');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validacao = $this->validator($request->all());
        if($validacao->fails()){
            return redirect()->back()
            ->witherrors($validacao->errors())
            ->withInput($request->all());
        }else{
            $permutas = new Permutar();
            $permutas->nome = $request->input('nome');
            $permutas->matricula = $request->input('matricula');
            $permutas->local = $request->input('local');
            $permutas->dia_do_servico = $request->input('dia');
            $permutas->hora_inicial = $request->input('das');
            $permutas->hora_final = $request->input('as');
            $permutas->escalado = "";
            $permutas->escaladoMatricula = "";
            $permutas->escaladoLocal = "";
            $permutas->escaladoDia_do_servico = "";
            $permutas->escaladoHora_inicial = "";
            $permutas->escaladoHora_final = "";
            $permutas->virtude = $request->input('virtude');
            $permutas->status = "Espera";
            $permutas->dataSPO = "";
            $permutas->assinaturaSPO = "";
            $permutas->optCMD = "";
            $permutas->assinaturaCMD = "";
            $permutas->save();
            return redirect()->route('permutas.index');
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Permutar  $permutar
     * @return \Illuminate\Http\Response
     */
    public function show(Permutar $permuta)
    {
        if($permuta->status == "Espera")
        {
            return view('permuta/permutaespera', compact('permuta'));
        }
        if($permuta->status == "Aceitar"){
            return view('permuta/permutasubistituto', compact('permuta'));
        }
        if($permuta->status == "Aceita" || $permuta->status == "Confirmada" || $permuta->status == "Confirmada pelo SPO")
        {
            return view('permuta/permutaAceita', compact('permuta'));
        }
        if($permuta->status == "Refazer" && $permuta->matricula == Auth::user()->matricula){
            return view('permuta/refazerPermuta', compact('permuta'));
        }
        if($permuta->status == "Refazer" && $permuta->matricula != Auth::user()->matricula){
            return view('erro');
        }
        if($permuta->status == "Confirmada e Finalizada" || $permuta->status == "Nâo Autorizada")
        {
            return view('permuta/permuta', compact('permuta'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Permutar  $permutar
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Permutar $permuta)
    {
        if($permuta->status == 'Refazer')
        {   
            $validacao = $this->validator($request->all());
            if($validacao->fails()){
                return redirect()->back()
                ->witherrors($validacao->errors())
                ->withInput($request->all());
            }else{
                DB::table('permutars')->where('id', $permuta->id)->update([
                    'nome'              => $request->input('nome'),
                    'matricula'         => $request->input('matricula'),
                    'local'             => $request->input('local'),
                    'dia_do_servico'    => $request->input('dia'),
                    'hora_inicial'      => $request->input('das'),
                    'hora_final'        => $request->input('as'), 
                    'virtude'           => $request->input('virtude'),
                    'status'            => 'Espera',
                ]);

                return redirect()->route('home');
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Permutar  $permutar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permutar $permuta)
    {
        $validacao = $this->validatorUpdate($request->all());
        if($validacao->fails()){
            return redirect()->back()
            ->witherrors($validacao->errors())
            ->withInput($request->all());
        }else{
            $permuta->nome = $request->input('nome');
            $permuta->matricula = $request->input('matricula');
            $permuta->local = $request->input('local');
            $permuta->dia_do_servico = $request->input('dia');
            $permuta->hora_inicial = $request->input('das');
            $permuta->hora_final = $request->input('as');
            $permuta->escalado = $request->input('nomesub');
            $permuta->escaladoMatricula = $request->input('matriculasub');
            $permuta->escaladoLocal = $request->input('localsub');
            $permuta->escaladoDia_do_servico = $request->input('diasub');
            $permuta->escaladoHora_inicial = $request->input('dassub');
            $permuta->escaladoHora_final = $request->input('assub');
            $permuta->virtude = $request->input('virtude');
            $permuta->status = "Aceita";
            $permuta->dataSPO = "";
            $permuta->assinaturaSPO = "";
            $permuta->optCMD = "";
            $permuta->assinaturaCMD = "";
            $permuta->save();
            return redirect()->route('permutas.index');
        }
    }

    public function refazerPermuta($permuta)
    {
       echo($permuta);
    }

    public function aceitar($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'    => 'Aceitar'
        ]);
        return redirect()->route('permutas.show', compact('id'));
    }

    public function atualizarStatus($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'    => 'Confirmada'
        ]);
        return redirect()->route('home');
    }

    public function SPO($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'        => 'Confirmada pelo SPO',
            'dataSpo'       => now(),
            'assinaturaSPO' => Auth::user()->nome
        ]);
        return redirect()->route('home');
    }

    public function nao($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'    => 'Nâo Autorizada'
        ]);
        return redirect()->route('home');
    }

    public function refazer($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'    => 'Refazer'
        ]);
        return redirect()->route('home');
    }

    public function CMD($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'    => 'Confirmada e Finalizada',
            'optCMD'       => 'Deferimento',
            'assinaturaCMD' => Auth::user()->nome
        ]);

        return redirect()->route('home');
    }

    public function naoCMD($id)
    {
        DB::table('permutars')->where('id', $id)->update([
            'status'    => 'Nâo Autorizada',
            'optCMD'       => 'Indeferimento',
            'assinaturaCMD' => Auth::user()->nome
        ]);

        return redirect()->route('home');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Permutar  $permutar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permutar $permuta)
    {
       
        
    }

    
    public function deletar($permuta)
    {
        DB::table('permutars')->where('id', $permuta)->delete();
        return redirect()->route('permutas.index');
    }

    public function validatorUpdate($date)
    {
        $regras = [
            'nome'                  => 'required',
            'matricula'             => 'required',
            'local'                 => 'required',
            'dia'                   => 'required',
            'das'                   => 'required',
            'as'                    => 'required',
            'nomesub'               => 'required',
            'matriculasub'          => 'required',
            'localsub'              => 'required',
            'diasub'                => 'required',
            'dassub'                => 'required',
            'assub'                 => 'required',
            'virtude'               => 'required',
        ];

        $mensagens = [
            'required'              => 'Campo Obrigatório',      
        ];

        return Validator::make($date, $regras, $mensagens);
    }

    public function validator($date)
    {
        $regras = [
            'nome'                  => 'required',
            'matricula'             => 'required',
            'local'                 => 'required',
            'dia'                   => 'required',
            'das'                   => 'required',
            'as'                    => 'required',
            'virtude'               => 'required',
        ];

        $mensagens = [
            'required'              => 'Campo Obrigatório',      
        ];

        return Validator::make($date, $regras, $mensagens);
    }

    public function imprimir(Permutar $permuta)
    {
        return view('permuta/gerar_pdf_Permuta', compact('permuta'));
    }
}
