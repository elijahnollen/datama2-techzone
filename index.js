require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { createClient } = require('@supabase/supabase-js');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_KEY);

// Forensic Audit Logger
const logAction = (action, details) => {
    const timestamp = new Date().toISOString();
    console.log(`[AUDIT LOG][${timestamp}] ACTION: ${action} | DETAILS: ${JSON.stringify(details)}`);
};

// --- TASK 1 & 4: AUTHENTICATION (ADMIN & CUSTOMER) ---

app.post('/api/admin-check', async (req, res) => {
    try {
        const { email, password } = req.body;
        const { data, error } = await supabase.from('employee').select('employee_role, first_name').eq('email_address', email?.trim().toLowerCase()).eq('password', password).single();
        if (error || !data) return res.status(401).json({ isAdmin: false, message: "Invalid Admin Credentials" });
        logAction("ADMIN_AUTH_SUCCESS", { email });
        res.json({ isAdmin: true, name: data.first_name, role: data.employee_role });
    } catch (err) { res.status(500).json({ error: "Server Error" }); }
});

app.post('/api/customer-login', async (req, res) => {
    try {
        const { email, password } = req.body;
        const { data, error } = await supabase.from('customer').select('customer_name').eq('email_address', email?.trim().toLowerCase()).eq('password', password).single();
        if (error || !data) return res.status(401).json({ success: false, message: "Invalid Customer Credentials" });
        logAction("CUSTOMER_LOGIN_SUCCESS", { email });
        res.json({ success: true, name: data.customer_name });
    } catch (err) { res.status(500).json({ error: "Server Error" }); }
});

// --- TASK 2: PRODUCT CATALOG & SEARCH ---

app.get('/api/products', async (req, res) => {
    try {
        const { search } = req.query;
        let query = supabase.from('product').select('*').eq('is_active', true);
        if (search) query = query.ilike('product_name', `%${search}%`);
        const { data, error } = await query.order('product_name', { ascending: true });
        res.json(data);
    } catch (err) { res.status(500).json({ error: "Search failed" }); }
});

// --- TASK 3: RETURN/REFUND MANAGEMENT ---

// Admin: View Pending Requests
app.get('/api/admin/refunds', async (req, res) => {
    try {
        const { data, error } = await supabase.from('refund_request').select('*, product(product_name), customer(customer_name)').eq('status', 'Pending');
        if (error) throw error;
        res.json(data);
    } catch (err) { res.status(500).json({ error: "Failed to fetch refunds" }); }
});

// Admin: Accept/Deny Refund
app.patch('/api/admin/refunds/:id', async (req, res) => {
    try {
        const { status } = req.body; 
        const { data, error } = await supabase.from('refund_request').update({ status, resolved_at: new Date().toISOString() }).eq('refund_id', req.params.id).select();
        if (error) throw error;
        logAction("REFUND_RESOLVED", { id: req.params.id, status });
        res.json({ message: `Refund ${status}`, request: data[0] });
    } catch (err) { res.status(500).json({ error: "Update failed" }); }
});

app.listen(PORT, () => console.log(`✅ TechZone Backend Live on Port ${PORT}`));