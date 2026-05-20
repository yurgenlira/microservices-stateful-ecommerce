module "ecr_app" {
  source = "../../modules/ecr"

  project_name = var.project_name
  service_name = "app"
}