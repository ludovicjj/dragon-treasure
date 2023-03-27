import React from 'react';
import PropTypes from "prop-types";

const Field = React.forwardRef(function (props, ref) {
    const {name, helpText, children} = props

    function handleClick(e) {
        e.preventDefault()
        const {current} = ref
        current.value = e.target.innerText
    }

    return <div className="mb-3">
        <label htmlFor={name} className="form-label">{children}</label>
        <input type="text" name={name} id={name} ref={ref} className="form-control"/>
        <div id={name + 'HelpBlock'} className="form-text">
            Try: <a href="#" onClick={handleClick}>{ helpText }</a>
        </div>
    </div>
})

export default class TreasureLogin extends React.Component {
    constructor(props) {
        super(props)
        this.handleSubmit = this.handleSubmit.bind(this);
        this.email = React.createRef()
        this.password = React.createRef()
        this.state = {
            error: ''
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        const email = this.email.current
        const password = this.password.current

        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value
            })
        })

        if (!response.ok) {
            const data = await response.json()
            this.setState({
                error: data.error
            })

            return;
        }

        // reset form fields
        this.setState({
            error: ''
        })
        email.value= ''
        password.value= ''
        this.props.userAuthenticated(response.headers.get('Location'))
    }

    render () {
        return <div className="card card-login">
            <div className="card-body">
                <form onSubmit={this.handleSubmit}>
                    {this.state.error && (
                        <div className="alert alert-danger">{this.state.error}</div>
                    )}
                    <Field name="email" ref={this.email} helpText={'bernie@dragonmail.com'}>Email</Field>
                    <Field name="password" ref={this.password} helpText={'roar'}>Password</Field>
                    <button className="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
        ;
    }
}

TreasureLogin.propTypes = {
    userAuthenticated: PropTypes.func.isRequired
}