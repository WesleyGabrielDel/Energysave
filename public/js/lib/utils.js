export const BASE_URL = "/EnergySaveProject/";
export const REQUEST_URL = "/EnergySaveProject";


export async function request(url, method = "GET", options = {}) {
    try {
        const response = await fetch(url, {
            method,
            credentials: "include",
            headers: {
                "Content-Type": "application/json",
                ...(options.headers || {})
            },
            body: options.body,
            ...options
        });

        let data;
        const text = await response.text();

        try {
            data = JSON.parse(text);
        } 
        catch {
            data = `=====================================\nErro Interno do Servidor\n=====================================\n${text}`;
        }

        if (!response.ok) {
            return {
                success: false,
                message: data?.message || data || "Erro na requisição"
            };
        }
        
        return data;
    } 
    
    catch (error) {
        return {
            success: false,
            message: `Erro de conexão com o servidor. \n${error}`
        };
    }
}

export function throwError(errorMessage, errorCode){
    let erro = new Error(errorMessage); 
    erro.code = errorCode; 

    console.log(errorMessage)
    throw erro; 
}