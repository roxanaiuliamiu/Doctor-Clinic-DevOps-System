{{- define "doctor-clinic.name" -}}
doctor-clinic
{{- end -}}

{{- define "doctor-clinic.fullname" -}}
{{ include "doctor-clinic.name" . }}
{{- end -}}