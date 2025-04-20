<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sub Pohon Keluarga</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
      /* Styles for tree and member cards */
      .tree > ul {
        display: flex;
        justify-content: center;
        padding-top: 20px;
      }
      .tree > ul > li {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
      }
      .tree ul ul {
        position: relative;
        padding-top: 20px;
        display: flex;
        justify-content: center;
      }
      .tree li {
        list-style-type: none;
        position: relative;
        padding: 20px 5px 0 5px;
        text-align: center;
      }
      .tree li::before,
      .tree li::after {
        content: "";
        position: absolute;
        top: 0;
        border-top: 2px solid #ccc;
        width: 50%;
        height: 20px;
      }
      .tree li::before {
        right: 50%;
        border-right: 2px solid #ccc;
      }
      .tree li::after {
        left: 50%;
        border-left: 2px solid #ccc;
      }
      .tree li:only-child::before,
      .tree li:only-child::after {
        content: none;
      }
      .tree li:only-child {
        padding-top: 0;
      }
      .tree li > .member {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        background: #0d6efd;
        color: white;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        flex-direction: row;
        min-width: 200px;
        max-width: 360px;
        justify-content: space-between;
        flex-wrap: wrap;
        cursor: pointer;
      }
      .tree li > .member.small {
        min-width: 160px;
        padding: 6px 10px;
      }
      .tree li > .member:hover {
        background: #084298;
      }
      .tree ul ul::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        border-left: 2px solid #ccc;
        height: 20px;
      }
      .member img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
      }
      .info {
        display: flex;
        flex-direction: column;
      }
      .actions {
        display: flex;
        gap: 3px;
      }
      .actions button {
        padding: 2px 5px;
        font-size: 0.75rem;
      }
    </style>
  <style>
    .card-biodata {
      margin-top: 10px;
      padding: 8px 12px;
      background: #f9f9f9;
      border-left: 3px solid #007bff;
      border-radius: 8px;
      font-size: 13px;
      line-height: 1.4;
      max-width: 300px;
      display: none; /* Initially hidden */
    }
  </style>
</head>
<body class="bg-light p-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>Sub Pohon Keluarga</h4>
      <a href="index.html" class="btn btn-outline-secondary">← Kembali</a>
    </div>
    <div id="familyTree" class="tree"></div>
    <div id="biodataContainer"></div> <!-- For displaying the biodata card -->
  </div>

  <script>
    const rootId = <?= $id ?>;
    let members = [];

    function buildTreeHTML(pid) {
      const kids = members.filter((m) => m.parent_id == pid);
      if (!kids.length) return "";
      let html = "<ul>";
      kids.forEach((c) => {
        const hasSp = !!c.spouse;
        html += `<li>
          <div class="member ${!hasSp ? "small" : ""}" onclick="showBiodata(${c.id})">
            <img src="${c.photo || 'https://via.placeholder.com/48'}" alt>
            <div class="info">
              <div>${c.name}</div>
              <small>${hasSp ? "❤️ " + c.spouse : ""}</small>
            </div>
            ${
              c.spouse_photo
                ? `<img src="${c.spouse_photo}" style="width:40px;height:40px;border-radius:50%;border:2px solid white">`
                : ""
            }
          </div>
          ${buildTreeHTML(c.id)}
        </li>`;
      });
      return html + "</ul>";
    }

    function renderTree() {
      const root = members.find((m) => m.id == rootId);
      if (!root) {
        document.getElementById("familyTree").innerHTML = "<p>Data tidak ditemukan.</p>";
        return;
      }

      const treeHTML = ` 
        <ul><li>
          <div class="member ${!root.spouse ? "small" : ""}" onclick="showBiodata(${root.id})">
            <img src="${root.photo || 'https://via.placeholder.com/48'}" alt>
            <div class="info">
              <div>${root.name}</div>
              <small>${root.spouse ? "❤️ " + root.spouse : ""}</small>
            </div>
            ${
              root.spouse_photo
                ? `<img src="${root.spouse_photo}" style="width:40px;height:40px;border-radius:50%;border:2px solid white">`
                : ""
            }
          </div>
          ${buildTreeHTML(root.id)}
        </li></ul>`;
      
      document.getElementById("familyTree").innerHTML = treeHTML;
    }

    function showBiodata(memberId) {
  console.log("showBiodata triggered for memberId:", memberId); // Debugging ID yang diklik
  const member = members.find(m => m.id === memberId);
  if (!member) {
    console.log("Member not found:", memberId); // Debugging jika member tidak ditemukan
    return;
  }

  console.log("Displaying biodata for:", member); // Debugging member yang ditemukan

  const biodataHTML = `
    <div class="card-biodata">
      <strong>Biodata ${member.name}</strong><br>
      <strong>📅 Tgl Lahir:</strong> ${member.birthdate || 'Belum diisi'}<br>
      <strong>📍 Alamat:</strong> ${member.address || 'Belum diisi'}<br>
      <strong>📞 Telepon:</strong> ${member.phone || 'Belum diisi'}
    </div>
  `;
  document.getElementById('biodataContainer').innerHTML = biodataHTML;
  document.querySelector('.card-biodata').style.display = 'block'; // Show biodata card
}


   fetch("get_members.php")
  .then((r) => r.json())
  .then((r) => {
    if (r.status === "success") {
      members = r.data;
      console.log("Members data:", members); // Debugging data anggota
      renderTree();
    } else {
      alert(r.message);
    }
  })
  .catch((e) => {
    console.error(e);
    alert("Gagal memuat data.");
  });

  </script>
</body>
</html>
