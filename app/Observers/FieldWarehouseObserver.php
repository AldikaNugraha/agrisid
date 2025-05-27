<?php

namespace App\Observers;

use App\Models\FieldWarehouse;
use App\Models\Warehouse;

class FieldWarehouseObserver
{
    /**
     * Handle the FieldWarehouse "created" event.
     */
    public function created(FieldWarehouse $fieldWarehouse): void
    {
        $this->updateWarehouseStock($fieldWarehouse->warehouse_id);
    }

    /**
     * Handle the FieldWarehouse "updated" event.
     */
    public function updated(FieldWarehouse $fieldWarehouse): void
    {
        // Only update if 'qty' has actually changed
        if ($fieldWarehouse->isDirty('qty')) {
            $this->updateWarehouseStock($fieldWarehouse->warehouse_id);
        }

        // If warehouse_id itself could change on an existing pivot record (uncommon)
        // and you need to update the stock of the old warehouse as well.
        if ($fieldWarehouse->isDirty('warehouse_id')) {
             $this->updateWarehouseStock($fieldWarehouse->warehouse_id); // New warehouse
            if ($fieldWarehouse->getOriginal('warehouse_id')) {
                $this->updateWarehouseStock($fieldWarehouse->getOriginal('warehouse_id')); // Old warehouse
            }
        }
    }

    /**
     * Handle the FieldWarehouse "deleted" event.
     */
    public function deleted(FieldWarehouse $fieldWarehouse): void
    {
        $this->updateWarehouseStock($fieldWarehouse->warehouse_id);
    }

    /**
     * Handle the FieldWarehouse "restored" event.
     */
    public function restored(FieldWarehouse $fieldWarehouse): void
    {
        $this->updateWarehouseStock($fieldWarehouse->warehouse_id);
    }

    /**
     * Handle the FieldWarehouse "force deleted" event.
     */
    public function forceDeleted(FieldWarehouse $fieldWarehouse): void
    {
        // If soft deletes are not used, 'deleted' covers this.
        // If they are, this handles permanent deletion after soft delete.
        $this->updateWarehouseStock($fieldWarehouse->warehouse_id);
    }

    /**
     * Trigger the stock update on the Warehouse model.
     */
    protected function updateWarehouseStock(?int $warehouseId): void
    {
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
            if ($warehouse) {
                $warehouse->updateCurrentStock();
            }
        }
    }
}
