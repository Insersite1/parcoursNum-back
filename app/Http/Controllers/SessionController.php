<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SessionController extends Controller
{
/**
 * Description: Récupère la liste de toutes les sessions avec leurs actions associées.
 * Méthode: GET
 * Entrée: Aucune
 * Sortie: Liste des sessions avec statut 200 en cas de succès.
 */
    public function index()
    {
        $sessions = Session::with('action')->get();
        return response()->json($sessions);
    }
/**
 * Description: Crée une nouvelle session.
 * Méthode: POST
 * Entrée: nom, description, image (facultatif), date_debut, date_fin, file (facultatif), par, action_id (facultatif)
 * Sortie: La session créée avec statut 201 en cas de succès.
 */
public function store(Request $request)
{
    $validatedData = $request->validate([
        'nom' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image',
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
        'file' => 'nullable|file',
        'par' => 'required|string|max:255',
        'action_id' => 'nullable|exists:actions,id',
    ]);
    
    // Créez la session
    $session = new Session();
    
    if ($request->hasFile('image')) {
        $couverture = $request->file('image');
        $avatarName = time() . '.' . $couverture->extension();
        $couverture->move(public_path('images'), $avatarName);
         $session->image = $avatarName;
    }
    if ($request->hasFile('file')) {
        $validatedData['file'] = $request->file('file')->store('files', 'public');
         $session->description = $validatedData['file'];
    }
         $session->nom = $validatedData['nom'];
         $session->description = $validatedData['description'];
         $session->date_debut = $validatedData['date_debut'];
         $session->date_fin = $validatedData['date_fin'];
         $session->par = $validatedData['par'];
         $session->action_id = $validatedData['action_id'];
    



    $session->save();

    return response()->json($session, 201);
}
/**
 * Description: Récupère les détails d'une session spécifique avec son action associée.
 * Méthode: GET
 * Entrée: Session (modèle de session)
 * Sortie: Détails de la session avec statut 200 en cas de succès.
 */
    public function show(Session $session)
    {
        return response()->json($session->load('action'));
    }

/**
 * Description: Met à jour une session existante.
 * Méthode: PUT
 * Entrée: nom, description, image (facultatif), date_debut, date_fin, file (facultatif), par, action_id (facultatif)
 * Sortie: La session mise à jour avec statut 200 en cas de succès.
 */
    public function update(Request $request, Session $session)
    {
        $validatedData = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|image|max:2048', 
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after_or_equal:date_debut',
            'file' => 'nullable|file|max:2048',
            'par' => 'sometimes|required|string|max:255',
            'action_id' => 'nullable|exists:actions,id',
        ]);

        if ($request->hasFile('image')) {
            if ($session->image) {
                Storage::disk('public')->delete($session->image);
            }
            $validatedData['image'] = $request->file('image')->store('images', 'public');
        } else {
            unset($validatedData['image']);
        }

        if ($request->hasFile('file')) {
            if ($session->file) {
                Storage::disk('public')->delete($session->file);
            }
            $validatedData['file'] = $request->file('file')->store('files', 'public');
        } else {
            unset($validatedData['file']);
        }

        $session->update($validatedData);

        return response()->json($session);
    }
/**
 * Description: Permet de télécharger ou mettre à jour les fichiers (image, fichier) d'une session.
 * Méthode: POST
 * Entrée: image (facultatif), file (facultatif)
 * Sortie: Message de confirmation avec statut 200 en cas de succès, session mise à jour.
 */
    public function uploadFile(Request $request, Session $session)
    {
        $validatedData = $request->validate([
            'image' => 'nullable|image|max:2048', 
            'file' => 'nullable|file|max:2048',  
        ]);

        if ($request->hasFile('image')) {
            $previousImage = $session->image;

            if ($previousImage) {
                Storage::disk('public')->delete($previousImage);
            }

            $validatedData['image'] = $request->file('image')->store('images', 'public');
        }

        if ($request->hasFile('file')) {
            $previousFile = $session->file;

            if ($previousFile) {
                Storage::disk('public')->delete($previousFile);
            }
            $validatedData['file'] = $request->file('file')->store('files', 'public');
        }

        $session->update($validatedData);

        return response()->json([
            'message' => 'Fichiers mis à jour avec succès',
            'session' => $session,
        ]);
    }
/**
 * Description: Supprime une session ainsi que ses fichiers associés (image et fichier).
 * Méthode: DELETE
 * Entrée: Session (modèle de session)
 * Sortie: Message de confirmation de suppression avec statut 200 en cas de succès.
 */
    public function destroy(Session $session)
    {
        if ($session->image) {
            Storage::disk('public')->delete($session->image);
        }
        if ($session->file) {
            Storage::disk('public')->delete($session->file);
        }

        $session->delete();

        return response()->json(['message' => 'Session supprimée avec succès']);
    }
/**
 * Description: Recherche des sessions en fonction d'un mot-clé dans plusieurs champs (nom, par, action, dates).
 * Méthode: GET
 * Entrée: search (terme de recherche)
 * Sortie: Liste des sessions correspondant au terme de recherche avec statut 200 en cas de succès.
 */
    public function search($search)
    {
        $query = Session::query();
        $query->where('nom', 'LIKE', '%' . $search . '%')
            ->orWhere('par', 'LIKE', '%' . $search . '%')
            ->orWhereHas('action', function ($subQuery) use ($search) {
                $subQuery->where('nom', 'LIKE', '%' . $search . '%'); 
            })
            ->orWhere(function ($dateQuery) use ($search) {
                $dateQuery->where('date_debut', 'LIKE', '%' . $search . '%')
                            ->orWhere('date_fin', 'LIKE', '%' . $search . '%');
            });
        $sessions = $query->with('action')->get();

        return response()->json($sessions);
    }

    public function getSceanceBySessionID($session_id)
    {
        // Recherche toutes les séances associées à la session donnée
        $sceances = Sceance::where('session_id', $session_id)->get();

        // Vérifie si aucune séance n'a été trouvée pour cette session
        if ($sceances->isEmpty()) {
            return response()->json(['message' => 'Aucune séance trouvée pour cette session'], 404);
        }

        // Retourne les séances trouvées au format JSON
        return response()->json($sceances);
    }

}

