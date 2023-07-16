import{r as l,R as e,H as h,I as E,d as f}from"./app.js";const y=({xmlFiles:n})=>{const[o,c]=l.useState(null),[r,u]=l.useState(""),[p,i]=l.useState(""),[m,s]=l.useState("xml"),d=t=>{if(t.preventDefault(),!o)return;const a=new FormData;a.append("file",o),a.append("shopName",r),a.append("shopLink",p),a.append("uploadType",m),f.Inertia.post("/api/upload",a)};return e.createElement("div",null,e.createElement(h,null,e.createElement("style",null,`
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

                    .home-upload-type-select{
                    padding: 10px;
                    }
                `)),e.createElement("div",{className:"home-container"},e.createElement("h1",null,"XLSX CONVERTER TO LINK"),e.createElement("div",null,e.createElement("p",null,"You are logged in."),e.createElement(E,{href:"/logout"},"Logout")),e.createElement("form",{className:"upload-form",encType:"multipart/form-data",onSubmit:d},e.createElement("select",{className:"home-upload-type-select",value:m,onChange:t=>s(t.target.value)},e.createElement("option",{value:"xlsx"},"upload and conver xslx file"),e.createElement("option",{value:"xml"},"only upload xml file")),e.createElement("br",null),e.createElement("input",{type:"file",onChange:t=>c(t.target.files[0])}),e.createElement("br",null),e.createElement("input",{type:"text",value:r,onChange:t=>u(t.target.value),placeholder:"Shop Name"}),e.createElement("br",null),e.createElement("input",{type:"text",value:p,onChange:t=>i(t.target.value),placeholder:"Shop Link"}),e.createElement("br",null),Array.isArray(n)&&n.length>0?n.map(t=>e.createElement("p",{key:t.id},t.filename)):e.createElement("p",null,"No file yet"),e.createElement("button",{type:"submit"},"Upload"))))};export{y as default};
