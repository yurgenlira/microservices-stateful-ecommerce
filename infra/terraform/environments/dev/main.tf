locals {
  s3_bucket_name = "${var.project_name}-${var.environment}-storage-${data.aws_caller_identity.current.account_id}-${var.aws_region}"
}

data "aws_caller_identity" "current" {}

data "aws_ecr_repository" "app" {
  name = "${var.project_name}/app"
}

module "vpc" {
  source = "../../modules/vpc"

  project_name         = var.project_name
  environment          = var.environment
  cidr_block           = "10.0.0.0/16"
  public_subnet_cidrs  = ["10.0.1.0/24", "10.0.2.0/24"]
  private_subnet_cidrs = ["10.0.10.0/24", "10.0.20.0/24"]
  azs                  = ["${var.aws_region}a", "${var.aws_region}b"]
}

module "security_groups" {
  source = "../../modules/security-groups"

  project_name = var.project_name
  vpc_id       = module.vpc.vpc_id
}

module "rds" {
  source = "../../modules/rds"

  project_name          = var.project_name
  environment           = var.environment
  subnet_ids            = module.vpc.private_subnet_ids
  security_group_id     = module.security_groups.rds_sg_id
  db_name               = var.db_name
  db_username           = var.db_username
  db_password           = var.db_password
  instance_class        = var.rds_instance_class
  allocated_storage     = 20
  multi_az              = var.environment == "prod"
  backup_retention_days = var.environment == "prod" ? 30 : 0 # Free tier: 0 disables automated backups; prod: 30 days
}

module "elasticache" {
  source = "../../modules/elasticache"

  project_name       = var.project_name
  environment        = var.environment
  subnet_ids         = module.vpc.private_subnet_ids
  security_group_id  = module.security_groups.redis_sg_id
  auth_token         = var.redis_auth_token
  node_type          = var.elasticache_node_type
  num_cache_clusters = var.environment == "prod" ? 2 : 1
}

module "app_runner" {
  source = "../../modules/app-runner"

  project_name          = var.project_name
  environment           = var.environment
  aws_region            = var.aws_region
  ecr_repository_url    = data.aws_ecr_repository.app.repository_url
  private_subnet_ids    = module.vpc.private_subnet_ids
  app_security_group_id = module.security_groups.app_sg_id

  environment_variables = {
    APP_ENV            = var.environment
    DB_CONNECTION      = "pgsql"
    DB_HOST            = module.rds.endpoint
    DB_PORT            = tostring(module.rds.port)
    DB_DATABASE        = module.rds.db_name
    DB_USERNAME        = var.db_username
    REDIS_HOST         = module.elasticache.endpoint
    REDIS_PORT         = tostring(module.elasticache.port)
    REDIS_SCHEME       = "tls"
    AWS_DEFAULT_REGION = var.aws_region
    AWS_BUCKET         = local.s3_bucket_name
    FILESYSTEM_DISK    = "s3"
    LOG_CHANNEL        = "stderr"
    APP_DEBUG          = "false"
  }

  environment_secrets = {
    DB_PASSWORD    = "arn:aws:ssm:${var.aws_region}:${data.aws_caller_identity.current.account_id}:parameter/${var.project_name}/${var.environment}/db_password"
    REDIS_PASSWORD = "arn:aws:ssm:${var.aws_region}:${data.aws_caller_identity.current.account_id}:parameter/${var.project_name}/${var.environment}/redis_auth_token"
    APP_KEY        = "arn:aws:ssm:${var.aws_region}:${data.aws_caller_identity.current.account_id}:parameter/${var.project_name}/${var.environment}/app_key"
    SENTRY_DSN     = "arn:aws:ssm:${var.aws_region}:${data.aws_caller_identity.current.account_id}:parameter/${var.project_name}/${var.environment}/sentry_dsn"
  }
}

module "s3" {
  source = "../../modules/s3"

  project_name                 = var.project_name
  environment                  = var.environment
  bucket_name                  = local.s3_bucket_name
  app_runner_instance_role_arn = module.app_runner.instance_role_arn
}