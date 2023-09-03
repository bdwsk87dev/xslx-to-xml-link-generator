import React, { useState } from 'react';
import { Inertia } from '@inertiajs/inertia';
import { InertiaLink, Head } from '@inertiajs/inertia-react';

const Home = ({ xmlFiles }) => {
    const [file, setFile] = useState(null);
    const [shopName, setShopName] = useState('');
    const [shopLink, setShopLink] = useState('');
    const [uploadType, setUploadType] = useState('xlsx');

    const handleSubmit = (e) => {
        e.preventDefault();

        if (!file) {
            return; // Файл не выбран, ничего не делаем
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('shopName', shopName);
        formData.append('shopLink', shopLink);
        formData.append('uploadType', uploadType);
        Inertia.post('/api/upload', formData);
    };

    return (
        <div>
            <Head>
                <style>{`
                    .home-container {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                        text-align: center;
                    }

                    .upload-form {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        margin-top: 20px;
                    }

                    .home-upload-type-select {
                        padding: 10px;
                        width: 245px;
                    }

                    input[type="file"] {
                        padding: 10px;
                        margin: 10px 0;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                    }

                    input[type="text"] {
                        padding: 10px;
                        margin: 5px 0;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                        width: 200px;
                    }

                    select {
                        padding: 10px;
                        margin: 5px 0;
                        border: 1px solid #ccc;
                        border-radius: 5px;
                        width: 200px;
                    }

                    button {
                        padding: 10px 20px;
                        margin-top: 10px;
                        background-color: #007bff;
                        color: #fff;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    button:hover {
                        background-color: #0056b3;
                    }

                    p {
                        margin: 5px 0;
                    }
                `}</style>
            </Head>
            <div className="home-container">
                <h1>XLSX CONVERTER TO LINK</h1>
                <div>
                    <p>You are logged in.</p>
                    <InertiaLink href="/logout">Logout</InertiaLink>
                </div>
                <form className="upload-form" encType="multipart/form-data" onSubmit={handleSubmit}>

                    <select className="home-upload-type-select" value={uploadType} onChange={(e) => setUploadType(e.target.value)}>
                        <option value="xlsx" selected>Upload and convert xlsx file</option>
                        <option value="xml">Only upload xml file</option>
                    </select>

                    <br />

                    <input type="file" onChange={(e) => setFile(e.target.files[0])} />
                    <br />
                    <input
                        type="text"
                        value={shopName}
                        onChange={(e) => setShopName(e.target.value)}
                        placeholder="Shop Name"
                    />
                    <br />
                    <input
                        type="text"
                        value={shopLink}
                        onChange={(e) => setShopLink(e.target.value)}
                        placeholder="Shop Link"
                    />

                    <br />

                    {Array.isArray(xmlFiles) && xmlFiles.length > 0 ? (
                        xmlFiles.map((xmlFile) => (
                            <p key={xmlFile.id}>{xmlFile.filename}</p>
                        ))
                    ) : (
                        <p>No file yet</p>
                    )}

                    <button type="submit">Upload</button>
                </form>
            </div>
        </div>
    );
};

export default Home;
