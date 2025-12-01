const express = require('express');
const { createClient } = require('@supabase/supabase-js');

const app = express();
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Use environment variables from Vercel
const supabaseUrl = process.env.SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_SERVICE_ROLE_KEY;
const supabase = createClient(supabaseUrl, supabaseKey);

app.post('/api/subscribe', async (req, res) => {
  const email = (req.body.email || '').trim();

  if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
    return res.status(400).json({ error: 'Invalid email' });
  }

  // check duplicate
  const { data: existing, error: selectError } = await supabase
    .from('subscribers')
    .select('id')
    .eq('email', email)
    .maybeSingle();

  if (selectError) {
    console.error(selectError);
    return res.status(500).json({ error: 'DB error' });
  }

  if (existing) {
    return res.status(409).json({ message: 'Already subscribed' });
  }

  const { error: insertError } = await supabase
    .from('subscribers')
    .insert([{ email }]);

  if (insertError) {
    console.error(insertError);
    return res.status(500).json({ error: 'DB error' });
  }

  return res.json({ message: 'Success' });
});

module.exports = app;
