import type { User } from './user.types'

export type ProjectStatus = 'OPEN' | 'IN_PROGRESS' | 'COMPLETED' | 'CANCELLED'

export interface Project {
  id: number
  client_id: number
  freelancer_id: number | null
  title: string
  description: string
  budget: number
  status: ProjectStatus
  created_at: string
  updated_at: string
  client?: User
  freelancer?: User
  attachments?: ProjectAttachment[]
}

export interface ProjectAttachment {
  id: number
  project_id: number
  title: string
  file_path: string
  created_at: string
  updated_at: string
}

export interface Proposal {
  id: number
  project_id: number
  freelancer_id: number
  bid_price: number
  estimated_time: string
  message: string
  status: 'PENDING' | 'ACCEPTED' | 'REJECTED'
  created_at: string
  updated_at: string
  freelancer?: User
}

export interface Submission {
  id: number
  project_id: number
  freelancer_id: number
  note: string
  status: 'PENDING_REVIEW' | 'APPROVED' | 'REVISION_REQUESTED'
  created_at: string
  updated_at: string
  attachments?: SubmissionAttachment[]
}

export interface SubmissionAttachment {
  id: number
  submission_id: number
  title: string
  file_path: string
  created_at: string
  updated_at: string
}
