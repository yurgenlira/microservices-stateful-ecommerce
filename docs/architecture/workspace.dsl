workspace "E-commerce Platform" "Microservices stateful e-commerce system" {

    model {
        customer = person "Customer" "End consumer shopping via web or mobile." "Customer"
        admin    = person "Admin"    "Internal team managing catalog, orders and inventory." "Admin"

        ecommercePlatform = softwareSystem "E-commerce Platform" "Laravel 13 monolith with DDD module structure." "System" {

            laravelApp = container "Laravel App" "DDD modular monolith" "PHP 8.5 / Laravel 13" "App Runner" { 
                userModule      = component "User Module"      "Registration, authentication, profile management." "Laravel Module"
                catalogModule   = component "Catalog Module"   "Product and category management."                 "Laravel Module"
                orderingModule  = component "Ordering Module"  "Order lifecycle from cart to delivery."           "Laravel Module"
                inventoryModule = component "Inventory Module" "Stock tracking and reservation."                  "Laravel Module"
            }

            rds         = container "Amazon RDS"         "Relational data"      "PostgreSQL 18 / VPC private" "Database"
            elasticache = container "Amazon ElastiCache" "Cache and sessions"   "Valkey 8.0 / VPC private"    "Database"
            s3Storage   = container "Amazon S3"          "Object storage"       "AWS S3"
            sentry      = container "Sentry"             "Error monitoring"     "SaaS"

        }

        customer -> laravelApp "Browses products, places orders" "HTTPS"
        admin    -> laravelApp "Manages catalog and inventory"   "HTTPS"

        orderingModule -> userModule      "UserServiceInterface"      "PublicApi / in-process"
        orderingModule -> catalogModule   "CatalogServiceInterface"   "PublicApi / in-process"
        orderingModule -> inventoryModule "InventoryServiceInterface" "PublicApi / in-process"

        laravelApp -> rds         "Domain data"   "TCP 5432 / TLS"
        laravelApp -> elasticache "Cache"         "TCP 6379 / TLS"
        laravelApp -> s3Storage   "File storage"  "HTTPS / IAM role"
        laravelApp -> sentry      "Error events"  "HTTPS"

        deploymentEnvironment "AWS — dev" {

            deploymentNode "GitHub Actions" "CI/CD pipeline" "github.com" {
                ciWorkflow = infrastructureNode "CI Workflow" "Build · Test · Deploy on push to main" "GitHub Actions"
            }

            deploymentNode "Sentry.io" "Error monitoring SaaS" "SaaS" {
                sentryNode = containerInstance sentry
            }

            deploymentNode "AWS" "Amazon Web Services" "Cloud" {
                deploymentNode "us-east-1 (N. Virginia)" "AWS Region" "AWS Region" {

                    deploymentNode "ECR (shared)" "Container image registry" "AWS Elastic Container Registry" "AWS" {
                        ecrRepo = infrastructureNode "ecommerce" "Stores production Docker images tagged by commit SHA" "ECR Repository"
                    }

                    deploymentNode "App Runner" "Managed compute — outside VPC" "AWS App Runner" "AWS" {
                        deploymentNode "Auto Scaling: 1–10 instances" "Scale-out when concurrency > 25 req/instance" "App Runner Scaling Policy" "AWS" {
                            appInstance = containerInstance laravelApp
                        }
                        vpcConnector = infrastructureNode "VPC Connector" "Routes private traffic to RDS and ElastiCache subnets" "App Runner VPC Connector"
                    }

                    deploymentNode "S3" "Managed object storage" "AWS S3" "AWS" {
                        s3Node = containerInstance s3Storage
                    }

                    deploymentNode "VPC ecommerce-dev (10.0.0.0/16)" "Isolated private network" "AWS VPC" "AWS" {

                        deploymentNode "Public Subnets — us-east-1a/b" "Internet-facing tier" "AWS Subnets" "AWS" {
                            igw = infrastructureNode "Internet Gateway" "Inbound/outbound internet access" "AWS IGW"
                            nat = infrastructureNode "NAT Gateway" "Outbound internet for private subnets" "AWS NAT Gateway"
                        }

                        deploymentNode "Private Subnets — us-east-1a/b" "Data tier — no direct public access" "AWS Subnets" "AWS" {

                            deploymentNode "RDS — db.t3.micro" "20 GB gp3 · 7-day backups · log_min_duration=500ms" "AWS RDS" "AWS" {
                                rdsNode = containerInstance rds
                            }

                            deploymentNode "ElastiCache — cache.t3.micro" "1 node · TLS + AUTH token · DB0=cache · DB1=sessions" "AWS ElastiCache" "AWS" {
                                cacheNode = containerInstance elasticache
                            }
                        }
                    }
                }
            }

            ciWorkflow -> ecrRepo       "docker push (SHA tag)"     "HTTPS / OIDC"
            ciWorkflow -> appInstance   "aws apprunner deploy"       "HTTPS / OIDC"
            vpcConnector -> rdsNode     "private routing"            "TCP 5432"
            vpcConnector -> cacheNode   "private routing"            "TCP 6379"
        }
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

        deployment ecommercePlatform "AWS — dev" "AWSDeployment" {
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
            element "App Runner" {
                background #388e3c
                color #ffffff
            }
            element "Database" {
                shape Cylinder
                background #e65100
                color #ffffff
            }
            element "AWS" {
                background #232f3e
                color #ffffff
            }
            element "Infrastructure Node" {
                shape RoundedBox
                background #546e7a
                color #ffffff
            }
        }
    }
}