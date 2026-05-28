<?php

namespace App\Http\Controllers\api;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $contacts = Contact::with('category')
            ->orderBy('created_at', 'asc')
            ->paginate(7);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create($request->validated());

        return new ContactResource($contact)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $contact = Contact::FindOrFail($id);
        } catch (ModelNotFoundException $e) {
            return abort(404, 'レコードが見つかりません');
        } 

        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreContactRequest $request, string $id)
    {
        try {
            $contact = Contact::findOrFail($id);
        }
        catch (ModelNotFoundException $e) {
            return abort(404, 'レコードが見つかりません');
        }

        $contac->update($request);

        return new ContactResource($contact)
            ->response()
            ->setStatusCode(Response::HTTP_UPDATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $contact = Contact::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return abort(404, 'レコードが見つかりません');
        }

        $contact->delete($id);

        return response()->json(null, 204);
    }
}
