graph TD
    subgraph Client_Level [Client Level: Presentation Layer]
        UI[User Interface: HTML5, Tailwind CSS, Vanilla JS]
        WebAPI[Browser APIs: Window Blur, Tab Tracking]
    end

    subgraph Server_Level [Server Level: Backend & Data]
        Router[PHP 8 Router]
        MVC[MVC Framework: Controllers & Models]
        DB[(MySQL Database)]
        Storage[File Uploads/Storage]
    end

    subgraph Microservice_Level [Microservice Level: Heavy Processing]
        Gemini[Gemini AI API: NLP Resume Analysis]
        FastAPI[Python FastAPI: OpenCV Haar Cascades]
    end

    %% Interactions
    UI <-->|HTTP/REST Requests| Router
    WebAPI -->|Violation Logs| Router
    Router <--> MVC
    MVC <--> DB
    MVC <--> Storage
    
    MVC -->|Resume Text (PDF) Analysis (optional)| Gemini
    MVC -->|Base64 Frames| FastAPI
