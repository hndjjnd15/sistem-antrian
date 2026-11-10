<!DOCTYPE html>
<html>
<body>

<form action="ambil.php" method="POST">
    <input type="hidden" name="cabang_id" value="1">

    <select name="layanan_id">
        <option value="1">Teller</option>
        <option value="2">Customer Service</option>
        <option value="3">Kredit</option>
    </select>

    <button type="submit">AMBIL NOMOR</button>
</form>

</body>
</html>