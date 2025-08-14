function convert() {
    const amount = parseFloat(document.getElementById('amount').value);
    const from = document.getElementById('fromCurrency').value;
    const to = document.getElementById('toCurrency').value;

    const rates ={
        HUF: 1,
        USD: 340,
        EUR:400

    };
    const amountInHUF = amount* rates[from];
    const converted = amountInHUF / rates[to];

    document.getElementById('result').innerText = `${converted.toFixed(2)} ${to}`; 
}
