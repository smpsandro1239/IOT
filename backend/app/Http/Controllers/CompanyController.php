<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Company::orderBy('name', 'asc');

        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('is_active_filter') && $request->is_active_filter !== 'all') {
            $query->where('is_active', (bool)$request->is_active_filter);
        }

        $companies = $query->withCount('sites')->paginate(15)->withQueryString(); // Adiciona contagem de sites
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
            'contact_email' => 'nullable|email|max:255|unique:companies,contact_email',
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        Company::create($validatedData);
        return redirect()->route('admin.companies.index')->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'contact_email' => 'nullable|email|max:255|unique:companies,contact_email,' . $company->id,
            // is_active é tratado abaixo
        ]);
        $validatedData['is_active'] = $request->boolean('is_active');

        $company->update($validatedData);
        return redirect()->route('admin.companies.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        try {
            // onDelete('cascade') na migration de 'sites' deve cuidar dos sites e barreiras associadas.
            $company->delete();
            return redirect()->route('admin.companies.index')->with('success', 'Empresa excluída com sucesso!');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Erro de Query ao excluir empresa ID {$company->id}: " . $e->getMessage());
            return redirect()->route('admin.companies.index')->with('error', 'Erro ao excluir empresa. Verifique se há entidades associadas (sites, etc.) se o onDelete cascade não estiver configurado corretamente ou se houver outras restrições.');
        } catch (\Exception $e) {
            Log::error("Erro geral ao excluir empresa ID {$company->id}: " . $e->getMessage());
            return redirect()->route('admin.companies.index')->with('error', 'Erro ao excluir empresa.');
        }
    }
}
