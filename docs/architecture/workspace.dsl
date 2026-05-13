workspace "E-commerce Platform" "Microservices stateful e-commerce system" {

    model {
        customer = person "Customer" "End consumer shopping via web or mobile." "Customer"
        admin    = person "Admin"    "Internal team managing catalog, orders and inventory." "Admin"

        ecommercePlatform = softwareSystem "E-commerce Platform" "Laravel 13 monolith with DDD module structure." "System" {

            laravelApp = container "Laravel Application" "Handles HTTP requests, business logic and data access." "PHP 8.5 / Laravel 13" "Application" {
                userModule      = component "User Module"      "Registration, authentication, profile management." "Laravel Module"
                catalogModule   = component "Catalog Module"   "Product and category management."                 "Laravel Module"
                orderingModule  = component "Ordering Module"  "Order lifecycle from cart to delivery."           "Laravel Module"
                inventoryModule = component "Inventory Module" "Stock tracking and reservation."                  "Laravel Module"
            }

            postgresDb = container "PostgreSQL 18" "Relational database. Tables partitioned by module domain." "PostgreSQL 18" "Database"
            redisStore = container "Redis 7.4"     "Cache (DB 0) and session store (DB 1)."                    "Redis 7.4"     "Cache"
        }

        customer -> laravelApp "Browses products, places orders" "HTTPS"
        admin    -> laravelApp "Manages catalog and inventory"   "HTTPS"

        laravelApp -> postgresDb "Reads and writes domain data"      "PDO / pgsql"
        laravelApp -> redisStore "Caches responses, stores sessions" "phpredis"

        userModule      -> postgresDb "users table"
        catalogModule   -> postgresDb "categories, products tables"
        orderingModule  -> postgresDb "orders, order_items tables"
        inventoryModule -> postgresDb "inventory_stocks, inventory_movements tables"
    }

    views {
        systemContext ecommercePlatform "SystemContext" {
            include *
            autoLayout tb
        }

        container ecommercePlatform "Containers" {
            include *
            autoLayout tb
        }

        component laravelApp "Components" {
            include *
            autoLayout tb
        }

        styles {
            element "Person" {
                shape Person
                background #1168bd
                color #ffffff
            }
            element "Customer" {
                background #1565c0
                color #ffffff
            }
            element "Admin" {
                background #4527a0
                color #ffffff
            }
            element "System" {
                background #2e7d32
                color #ffffff
            }
            element "Application" {
                background #388e3c
                color #ffffff
            }
            element "Database" {
                shape Cylinder
                background #e65100
                color #ffffff
            }
            element "Cache" {
                shape Cylinder
                background #c62828
                color #ffffff
            }
        }
    }
}
