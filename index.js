require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { createClient } = require('@supabase/supabase-js');

// --- SECURITY CHECK ---
// Ensures the server doesn't start with missing credentials
if (!process.env.SUPABASE_URL || !process.env.SUPABASE_KEY) {
    console.error("❌ CRITICAL ERROR: Supabase credentials missing in .env file.");
    process.exit(1);
}

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Initialize Supabase Connection
const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);

// Helper for Forensic Logging
// Vital for creating an audit trail of system actions
const logAction = (action, details) => {
    const timestamp = new Date().toISOString();
    console.log(`[AUDIT LOG][${timestamp}] ACTION: ${action} | DETAILS: ${JSON.stringify(details)}`);
};

// --- ROUTES ---

// 1. Home Route
app.get('/', (req, res) => {
    res.send('TechZone Backend is Live! 🚀');
});

// 2. Product Catalog & Search
app.get('/api/products', async (req, res) => {
    try {
        const { search } = req.query;
        let query = supabase.from('product').select('*').eq('is_active', true);

        if (search) {
            query = query.ilike('product_name', `%${search}%`);
        }

        const { data, error } = await query;
        if (error) throw error;
        res.json(data);
    } catch (err) {
        console.error("Fetch Products Error:", err.message);
        res.status(500).json({ error: "Failed to fetch products" });
    }
});

// 3. Admin Authentication Check
app.post('/api/admin-check', async (req, res) => {
    try {
        const { email } = req.body;
        const { data, error } = await supabase
            .from('employee')
            .select('employee_role')
            .eq('email_address', email)
            .single();

        if (error || !data) {
            logAction("LOGIN_ATTEMPT_FAILED", { email, reason: "User not found" });
            return res.status(404).json({ isAdmin: false, message: "User not found" });
        }

        const adminRoles = ['Store Manager', 'Admin', 'System'];
        const isAdmin = adminRoles.includes(data.employee_role);
        
        logAction("ADMIN_AUTH_SUCCESS", { email, role: data.employee_role });
        res.json({ isAdmin, role: data.employee_role });
    } catch (err) {
        res.status(500).json({ error: "Internal Server Error" });
    }
});

// 4. Fetch all Returns (For Admin Dashboard)
app.get('/api/returns', async (req, res) => {
    try {
        const { data, error } = await supabase
            .from('return_item')
            .select(`
                return_itemID,
                return_quantity,
                reason,
                return_status,
                notes,
                return_transaction (
                    return_date,
                    customer (first_name, last_name)
                )
            `);

        if (error) throw error;
        res.json(data);
    } catch (err) {
        res.status(400).json({ error: err.message });
    }
});

// 5. Approve/Update Return Status
app.post('/api/approve-return', async (req, res) => {
    try {
        const { returnItemID, newStatus } = req.body;

        const { data, error } = await supabase
            .from('return_item')
            .update({ return_status: newStatus })
            .eq('return_itemID', returnItemID)
            .select();

        if (error) throw error;

        logAction("RETURN_APPROVED", { returnItemID, newStatus });
        res.json({ message: "Return status updated successfully!", updatedRecord: data });
    } catch (err) {
        res.status(400).json({ error: err.message });
    }
});

// 6. Inventory Transaction History
// Provides the forensic trail of all stock changes
app.get('/api/inventory-history', async (req, res) => {
    try {
        const { data, error } = await supabase
            .from('inventory_transaction')
            .select(`
                transID,
                quantity_change,
                transaction_type,
                product (
                    product_name
                )
            `)
            .order('transID', { ascending: false });

        if (error) throw error;
        res.json(data);
    } catch (err) {
        console.error("History Fetch Error:", err.message);
        res.status(500).json({ error: "Failed to retrieve inventory logs" });
    }
});

// 7. BUSINESS LOGIC: Inventory Insights
// Calculates stock metrics and generates low-stock alerts
app.get('/api/inventory-insights', async (req, res) => {
    try {
        const { data, error } = await supabase
            .from('product')
            .select('product_name, quantity, selling_price')
            .eq('is_active', true);

        if (error) throw error;

        const lowStockItems = data.filter(item => item.quantity < 10);
        const totalStockValue = data.reduce((sum, item) => sum + (item.quantity * item.selling_price), 0);

        const summary = {
            total_products: data.length,
            low_stock_count: lowStockItems.length,
            estimated_inventory_value: totalStockValue
        };

        logAction("GENERATE_INSIGHTS", summary);
        res.json({ summary, alerts: lowStockItems });

    } catch (err) {
        console.error("Insights Error:", err.message);
        res.status(500).json({ error: "Failed to generate inventory insights" });
    }
});

// Start Server
app.listen(PORT, () => {
    console.log(`✅ Server running on http://localhost:${PORT}`);
});