graph LR
    SA((Super Admin))
    style SA fill:#f9f,stroke:#333,stroke-width:2px

    subgraph Access_Admin ["Access"]
        SA1(Login)
        SA2(Logout)
    end

    subgraph Overview
        SA3(View System Stats)
    end

    subgraph Canteen_Governance ["Canteen Governance"]
        SA4(View All Canteens)
        SA5(Toggle Canteen Status)
        SA6(Delete Canteen)
    end

    subgraph User_Governance ["User Governance"]
        SA7(View All Users)
        SA8(Delete User)
        SA9(View Contact Messages)
        SA10(Delete Message)
    end

    SA --> SA1
    SA --> SA2
    SA --> SA3
    SA --> SA4
    SA --> SA5
    SA --> SA6
    SA --> SA7
    SA --> SA8
    SA --> SA9
    SA --> SA10
