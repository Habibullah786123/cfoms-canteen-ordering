graph LR
    CO((Canteen Owner))
    style CO fill:#f9f,stroke:#333,stroke-width:2px

    subgraph Access
        CO1(Login)
        CO2(Logout)
        CO3(Update Profile)
    end

    subgraph Dashboard
        CO4(View Sales Stats)
        CO5(View Pending Orders Count)
    end

    subgraph Menu_Management ["Menu Management"]
        CO6(Add Menu Item)
        CO7(Edit Menu Item)
        CO8(Delete Menu Item)
        CO9(Toggle Availability)
    end

    subgraph Order_Management ["Order Management"]
        CO10(View Incoming Orders)
        CO11(Update Order Status)
    end
    
    subgraph Feedback
        CO12(View Reviews)
    end

    CO --> CO1
    CO --> CO2
    CO --> CO3
    CO --> CO4
    CO --> CO5
    CO --> CO6
    CO --> CO7
    CO --> CO8
    CO --> CO9
    CO --> CO10
    CO --> CO11
    CO --> CO12
