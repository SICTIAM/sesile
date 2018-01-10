import React from 'react'
import UserAvatar from 'react-user-avatar'
import DatePicker from 'react-datepicker'

const Form = ({ children, onSubmit }) =>
    <form onKeyDown={e => {
          if (e.key === 'Enter') {
            e.preventDefault()
            onSubmit()
          }
        }
      }
      onSubmit={e => {
          e.preventDefault()
          e.stopPropagation()
        }
      }>
        {children}
    </form>

const FormGroup = ({ children }) =>
    <div class="grid-container">
        <div className="grid-x grid-padding-x">
            {children}
        </div>
    </div>

const Input = ({ type, id, value, placeholder, onChange, className, onBlur, labelText, helpText, children }) =>
    <div className={className}>
        <label>
            {labelText}
            <input  id={id}
                    type={type}
                    name={id}
                    value={value}
                    placeholder={placeholder}  
                    onChange={e => {
                        let { value } = e.target
                        if(type === 'number') value = parseInt(value)
                        onChange(e.target.name, value)
                    }} 
                    onBlur={onBlur} />
            {children ? children : <p className="help-text" id={id}>{helpText}</p> }
        </label>
    </div>

const Textarea = ({ id, name, value, placeholder, onChange, className, onBlur, labelText, helpText, children }) =>
    <div className={className}>
        <label>
            {labelText}
            <textarea   id={id}
                        name={name}
                        placeholder={placeholder}
                        onChange={e => {
                            let { value } = e.target
                            onChange(e.target.name, value)
                        }}
                        onBlur={onBlur}
                        value={value}
            >
            </textarea>
                {children ? children : <p className="help-text" id={id}>{helpText}</p> }

        </label>
    </div>

const InputFile = ({ id, className, labelText, accept, onChange}) => 
    <div className={className}>
        <label htmlFor={id} className="button">{labelText}</label>
        <input type="file" id={id} className="show-for-sr" accept={accept} onChange={e => onChange(e.target.files[0])} />
    </div>

const Button = ({ id, className, classNameButton, labelText, onClick, disabled }) =>
    <div className={className}>
        <button id={id} className={classNameButton + " button"} disabled={disabled} onClick={() => onClick()}>{labelText}</button>
    </div>

const ButtonConfirm = ({ id, className, labelButton, confirmationText, labelConfirmButton, handleClickConfirm, disabled }) =>
    <div className={className}>
        <button className="alert button" disabled={disabled} data-toggle={id}>{labelButton}</button>
        <div className="dropdown-pane" id={id} data-dropdown data-auto-focus="true"> 
            <button className="close-button" type="button" onClick={e => $("#" + id).foundation('close')}>
                <span aria-hidden="true">&times;</span>
            </button>
            <div className="text-left">
                <span>{confirmationText}</span>
            </div>
            <Button className="medium-6 columns"
                    classNameButton="alert"
                    labelText={labelConfirmButton}
                    onClick={handleClickConfirm}/>
        </div>
    </div>

const Switch = ({ id, className, onChange, labelText, checked, activeText, inactiveText }) =>
    <div className={ className + " checkbox"}>
        <label htmlFor={id}>{labelText}</label>
        <div className="switch">
            <input className="switch-input" id={id} type="checkbox" name={id} checked={checked} onChange={e => onChange(e.target.name, e.target.checked)}/>
            <label className="switch-paddle" htmlFor="active">
                <span className="switch-active" aria-hidden="true">{activeText}</span>
                <span className="switch-inactive" aria-hidden="true">{inactiveText}</span>
            </label>
        </div>
    </div>

const Avatar = ({ className, size = 100, nom, fileName }) =>
    fileName ?
        <div className={className}>
            <UserAvatar size={size} name={nom.charAt(0) || "S"} src={fileName} />
        </div> 
        :
        <div className={className}>
            <UserAvatar size={size} name={nom.charAt(0) || "S"} colors={['#fe5e3a', '#404257', '#34a3fc']}/>
        </div>

const Select = ({ id, className, label, value, onChange, children }) =>
    <div className={className}>
        <label htmlFor={id}>{label}</label>
        <select id={id} value={value} onChange={(e) => onChange(id, e.target.value)} >
            {children}
        </select>
    </div>

const InputDatePicker = ({id, className, date, label, onChange, i18nextLng, onBlur, readOnly=false, minDate, maxDate}) =>
    <div className={className}>
        <label htmlFor={id}>{label}</label>
        <DatePicker id={id}
                    selected={ date }
                    onChange={ onChange }
                    readOnly={readOnly}
                    onBlur={onBlur}
                    minDate={minDate}
                    maxDate={maxDate}
                    locale={i18nextLng || window.localStorage.i18nextLng}/>
    </div>


export { Form, FormGroup, Input, Textarea, InputFile, Button, ButtonConfirm, Switch, Avatar, Select, InputDatePicker }
