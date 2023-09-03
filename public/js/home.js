import{r as n,R as e,H as x,I as g,d as f}from"./app.js";const E=({xmlFiles:l})=>{const[r,c]=n.useState(null),[o,d]=n.useState(""),[p,u]=n.useState(""),[i,m]=n.useState("xlsx"),s=t=>{if(t.preventDefault(),!r)return;const a=new FormData;a.append("file",r),a.append("shopName",o),a.append("shopLink",p),a.append("uploadType",i),f.Inertia.post("/api/upload",a)};return e.createElement("div",null,e.createElement(x,null,e.createElement("style",null,`
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
                `)),e.createElement("div",{className:"home-container"},e.createElement("h1",null,"XLSX CONVERTER TO LINK"),e.createElement("div",null,e.createElement("p",null,"You are logged in."),e.createElement(g,{href:"/logout"},"Logout")),e.createElement("form",{className:"upload-form",encType:"multipart/form-data",onSubmit:s},e.createElement("select",{className:"home-upload-type-select",value:i,onChange:t=>m(t.target.value)},e.createElement("option",{value:"xlsx",selected:!0},"Upload and convert xlsx file"),e.createElement("option",{value:"xml"},"Only upload xml file")),e.createElement("br",null),e.createElement("input",{type:"file",onChange:t=>c(t.target.files[0])}),e.createElement("br",null),e.createElement("input",{type:"text",value:o,onChange:t=>d(t.target.value),placeholder:"Shop Name"}),e.createElement("br",null),e.createElement("input",{type:"text",value:p,onChange:t=>u(t.target.value),placeholder:"Shop Link"}),e.createElement("br",null),Array.isArray(l)&&l.length>0?l.map(t=>e.createElement("p",{key:t.id},t.filename)):e.createElement("p",null,"No file yet"),e.createElement("button",{type:"submit"},"Upload"))))};export{E as default};
