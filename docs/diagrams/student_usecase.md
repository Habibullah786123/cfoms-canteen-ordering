graph LR
    U((Student))
    style U fill:#f9f,stroke:#333,stroke-width:2px

    subgraph Authentication
        UC1(Register Account)
        UC2(Login)
        UC3(Logout)
        UC4(Manage Profile)
    end

    subgraph Ordering_System ["Ordering System"]
        UC5(Browse Canteens)
        UC6(View Menu Items)
        UC7(Add Items to Cart)
        UC8(View Cart)
        UC9(Checkout / Place Order)
    end

    subgraph Post_Order ["Post-Order"]
        UC10(Track Order Status)
        UC11(View Order History)
        UC12(Rate/Review Order)
    end

    subgraph Support
        UC13(Contact Admin)
    end

    U --> UC1
    U --> UC2
    U --> UC3
    U --> UC4
    U --> UC5
    U --> UC6
    U --> UC7
    U --> UC8
    U --> UC9
    U --> UC10
    U --> UC11
    UC11 -.-> UC12
    U --> UC13
