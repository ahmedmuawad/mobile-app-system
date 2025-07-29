namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\SparePart;
use Illuminate\Http\Request;

class RepairController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'device_type' => 'required|string|max:255',
            'status' => 'required|string',
            'problem_description' => 'required|string',
            'repair_type' => 'required|in:software,hardware,both',
            'repair_cost' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'spare_part_ids' => 'nullable|array',
            'spare_part_ids.*' => 'exists:spare_parts,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
        ]);

        $repair = Repair::create([
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'device_type' => $data['device_type'],
            'status' => $data['status'],
            'problem_description' => $data['problem_description'],
            'repair_type' => $data['repair_type'],
            'repair_cost' => $data['repair_cost'],
            'discount' => $data['discount'] ?? 0,
            'paid' => $data['paid'] ?? 0,
        ]);

        // ربط قطع الغيار مع الكميات
        if (!empty($data['spare_part_ids'])) {
            $syncData = [];
            foreach ($data['spare_part_ids'] as $partId) {
                $qty = $data['quantities'][$partId] ?? 1;
                $syncData[$partId] = ['quantity' => $qty];
            }
            $repair->spareParts()->sync($syncData);
        }

        return redirect()->route('admin.repairs.index')->with('success', 'تم إضافة الفاتورة بنجاح');
    }

    public function update(Request $request, Repair $repair)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'device_type' => 'required|string|max:255',
            'status' => 'required|string',
            'problem_description' => 'required|string',
            'repair_type' => 'required|in:software,hardware,both',
            'repair_cost' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'spare_part_ids' => 'nullable|array',
            'spare_part_ids.*' => 'exists:spare_parts,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
        ]);

        $repair->update([
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'device_type' => $data['device_type'],
            'status' => $data['status'],
            'problem_description' => $data['problem_description'],
            'repair_type' => $data['repair_type'],
            'repair_cost' => $data['repair_cost'],
            'discount' => $data['discount'] ?? 0,
            'paid' => $data['paid'] ?? 0,
        ]);

        if (!empty($data['spare_part_ids'])) {
            $syncData = [];
            foreach ($data['spare_part_ids'] as $partId) {
                $qty = $data['quantities'][$partId] ?? 1;
                $syncData[$partId] = ['quantity' => $qty];
            }
            $repair->spareParts()->sync($syncData);
        } else {
            $repair->spareParts()->detach();
        }

        return redirect()->route('admin.repairs.index')->with('success', 'تم تحديث الفاتورة بنجاح');
    }
}
