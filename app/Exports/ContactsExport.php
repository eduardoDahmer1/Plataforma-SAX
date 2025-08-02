<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    protected $type;

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function collection()
    {
        $query = Contact::query();

        if ($this->type && in_array($this->type, ['1', '2'])) {
            $query->where('contact_type', $this->type);
        }

        return $query->select('name', 'email', 'phone', 'message', 'contact_type', 'created_at')->get();
    }

    public function headings(): array
    {
        return ['Nome', 'Email', 'Telefone', 'Mensagem', 'Tipo de Contato', 'Data'];
    }
}
