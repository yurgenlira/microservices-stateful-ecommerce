output "app_runner_service_url" {
  value = module.app_runner.service_url
}

output "app_runner_service_arn" {
  value = module.app_runner.service_arn
}

output "ecr_access_role_arn" {
  value = module.app_runner.ecr_access_role_arn
}

output "s3_bucket_name" {
  value = module.s3.bucket_name
}